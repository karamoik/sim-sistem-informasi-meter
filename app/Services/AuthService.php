<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    public function login(array $credentials, ?array $deviceInfo = null): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->locked_until && $user->locked_until->isFuture()) {
            $minutes = ceil($user->locked_until->diffInSeconds(now()) / 60);
            throw new AuthenticationException(
                "Akun terkunci. Coba lagi dalam {$minutes} menit."
            );
        }

        if ($user && !$user->is_active) {
            throw new AuthenticationException('Akun anda telah dinonaktifkan. Hubungi administrator.');
        }

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            if ($user) {
                $attempts = $user->failed_login_attempts + 1;
                $updates = ['failed_login_attempts' => $attempts];

                if ($attempts >= self::MAX_ATTEMPTS) {
                    $updates['locked_until'] = now()->addMinutes(self::LOCKOUT_MINUTES);
                }

                $user->update($updates);
            }

            throw new AuthenticationException('Email atau password salah.');
        }

        $user->update([
            'failed_login_attempts' => 0,
            'locked_until'          => null,
        ]);

        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');

        if ($user->two_factor_enabled) {
            $pendingToken = $guard->claims([
                '2fa_pending' => true,
                'exp'         => now()->addMinutes(5)->timestamp,
            ])->login($user);

            return [
                'two_factor_required' => true,
                'two_factor_token'    => $pendingToken,
                'user'                => ['name' => $user->name, 'email' => $user->email],
            ];
        }

        $token = $guard->login($user);

        $sessionId = null;
        if ($deviceInfo) {
            $sessionId = $this->createSession($user, $deviceInfo);
        }

        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => (int) config('jwt.ttl') * 60,
            'session_id'   => $sessionId,
            'user'         => $user,
        ];
    }

    public function register(array $data): array
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        $token = $guard->login($user);

        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => (int) config('jwt.ttl') * 60,
            'user'         => $user,
        ];
    }

    public function logout(): void
    {
        $fingerprintId = request()->header('X-Session-Fingerprint');
        $user = auth('api')->user();

        if ($user && $fingerprintId) {
            DB::transaction(function () use ($user, $fingerprintId) {
                User::query()->whereKey($user->id)->lockForUpdate()->first();

                $session = UserSession::where('user_id', $user->id)
                    ->where('fingerprint_id', $fingerprintId)
                    ->active()
                    ->first();

                if ($session) {
                    $wasMain = $session->is_main;
                    $session->update(['is_active' => false]);

                    if ($wasMain) {
                        $nextSession = UserSession::where('user_id', $user->id)
                            ->active()
                            ->orderByDesc('last_active_at')
                            ->first();

                        if ($nextSession) {
                            $this->makeMain($nextSession);
                            $nextSession->update(['is_main' => true]);
                        }
                    }
                }
            });
        }

        auth('api')->logout();
    }

    public function refresh(): array
    {
        $guard = auth('api');
        $token = $guard->refresh();

        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => (int) config('jwt.ttl') * 60,
            'user'         => $guard->user(),
        ];
    }

    public function me(): User
    {
        return auth('api')->user();
    }

    private function createSession(User $user, array $deviceInfo): int
    {
        return DB::transaction(function () use ($user, $deviceInfo) {
            User::query()->whereKey($user->id)->lockForUpdate()->first();

            $fingerprintId = $deviceInfo['fingerprint_id'] ?? md5(uniqid('', true));
            $deviceName = $this->buildDeviceName($deviceInfo);
            $deviceInfo = $this->withDeviceSignature($deviceInfo);

            $existingSession = UserSession::where('user_id', $user->id)
                ->where('fingerprint_id', $fingerprintId)
                ->first();

            if ($existingSession) {
                $existingSession->update([
                    'is_active' => true,
                    'last_active_at' => now(),
                    'ip_address' => $deviceInfo['ip_address'] ?? request()->ip(),
                    'user_agent' => $deviceInfo['user_agent'] ?? request()->userAgent(),
                    'device_name' => $deviceName,
                    'device_info' => $deviceInfo,
                ]);

                $this->ensureMainStatus($existingSession);
                return $existingSession->id;
            }

            $recognizedSession = UserSession::where('user_id', $user->id)
                ->where('is_active', true)
                ->get()
                ->first(fn (UserSession $s) => $this->matchesDeviceSignature($s, $deviceInfo));

            if ($recognizedSession) {
                $recognizedSession->update([
                    'fingerprint_id' => $fingerprintId,
                    'is_active' => true,
                    'last_active_at' => now(),
                    'ip_address' => $deviceInfo['ip_address'] ?? request()->ip(),
                    'user_agent' => $deviceInfo['user_agent'] ?? request()->userAgent(),
                    'device_name' => $deviceName,
                    'device_info' => $deviceInfo,
                ]);

                $this->ensureMainStatus($recognizedSession);
                return $recognizedSession->id;
            }

            $session = UserSession::create([
                'user_id' => $user->id,
                'fingerprint_id' => $fingerprintId,
                'device_name' => $deviceName,
                'device_info' => $deviceInfo,
                'ip_address' => $deviceInfo['ip_address'] ?? request()->ip(),
                'user_agent' => $deviceInfo['user_agent'] ?? request()->userAgent(),
                'is_main' => false,
                'is_active' => true,
                'last_active_at' => now(),
            ]);

            $this->ensureMainStatus($session);

            return $session->id;
        });
    }

    private function ensureMainStatus(UserSession $session): void
    {
        $hasOtherActive = UserSession::where('user_id', $session->user_id)
            ->where('id', '!=', $session->id)
            ->active()
            ->exists();

        if ($session->is_main && !$hasOtherActive) {
            $this->makeMain($session);
            return;
        }

        if (!$session->is_main && !$hasOtherActive) {
            $this->makeMain($session);
            $session->update(['is_main' => true]);
        }
    }

    private function makeMain(UserSession $session): void
    {
        UserSession::where('user_id', $session->user_id)
            ->where('id', '!=', $session->id)
            ->where('is_main', true)
            ->update(['is_main' => false]);
    }

    private function withDeviceSignature(array $deviceInfo): array
    {
        $deviceInfo['device_signature'] = hash('sha256', implode('|', [
            $deviceInfo['os'] ?? '',
            $deviceInfo['browser'] ?? '',
            $deviceInfo['device_type'] ?? '',
            $deviceInfo['screen'] ?? '',
            $deviceInfo['timezone'] ?? '',
            $deviceInfo['language'] ?? '',
            $deviceInfo['platform'] ?? '',
            $deviceInfo['webgl_renderer'] ?? '',
        ]));

        return $deviceInfo;
    }

    private function matchesDeviceSignature(UserSession $session, array $deviceInfo): bool
    {
        $stored = $session->device_info ?? [];

        if (($stored['device_signature'] ?? null) !== null) {
            return ($stored['device_signature'] ?? '') === ($deviceInfo['device_signature'] ?? '');
        }

        foreach (['os', 'browser', 'device_type', 'screen', 'timezone', 'language'] as $key) {
            $a = $stored[$key] ?? null;
            $b = $deviceInfo[$key] ?? null;
            if ($a === null || $b === null || $a !== $b) {
                return false;
            }
        }

        return true;
    }

    private function buildDeviceName(array $deviceInfo): string
    {
        $os = $deviceInfo['os'] ?? 'Unknown OS';
        $browser = $deviceInfo['browser'] ?? 'Unknown Browser';
        return "{$os} - {$browser}";
    }
}

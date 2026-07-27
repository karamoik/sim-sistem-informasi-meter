<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;

class TwoFactorController extends BaseApiController
{
    public function __construct(
        protected TwoFactorService $twoFactorService
    ) {}

    public function setup(Request $request)
    {
        $user = $request->user();

        $secret = $this->twoFactorService->generateSecretKey();

        $user->update(['two_factor_secret' => $secret]);

        $qrCodeUrl = $this->twoFactorService->getQRCodeUrl($user, $secret);

        return $this->success([
            'secret'      => $secret,
            'qr_code_url' => $qrCodeUrl,
        ], '2FA setup initiated');
    }

    public function enable(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if (!$user->two_factor_secret) {
            return $this->error('Silakan inisialisasi 2FA terlebih dahulu.', null, 400);
        }

        if ($user->two_factor_enabled) {
            return $this->error('2FA sudah aktif.', null, 400);
        }

        $valid = $this->twoFactorService->verifyCode(
            $user->two_factor_secret,
            $request->code
        );

        if (!$valid) {
            return $this->error('Kode verifikasi salah. Silakan coba lagi.', null, 422);
        }

        $user->update(['two_factor_enabled' => true]);

        return $this->success(null, '2FA berhasil diaktifkan');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code'            => ['required', 'string', 'size:6'],
            'two_factor_token' => ['required', 'string'],
        ]);

        $payload = auth('api')->setToken($request->two_factor_token)->payload();

        if (!$payload) {
            return $this->error('Token tidak valid.', null, 401);
        }

        $userId = $payload->get('sub');
        $user = User::find($userId);

        if (!$user || !$user->two_factor_enabled) {
            return $this->error('User tidak valid.', null, 401);
        }

        $valid = $this->twoFactorService->verifyCode(
            $user->two_factor_secret,
            $request->code
        );

        if (!$valid) {
            return $this->error('Kode 2FA salah. Silakan coba lagi.', null, 422);
        }

        $finalToken = auth('api')->login($user);

        return $this->success([
            'access_token' => $finalToken,
            'token_type'   => 'Bearer',
            'expires_in'   => (int) config('jwt.ttl') * 60,
            'user'         => $user,
        ], 'Verifikasi 2FA berhasil');
    }

    public function status(Request $request)
    {
        $user = $request->user();

        return $this->success([
            'enabled' => $user->two_factor_enabled,
        ], '2FA status');
    }

    public function disable(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if (!$user->two_factor_enabled) {
            return $this->error('2FA belum aktif.', null, 400);
        }

        $valid = $this->twoFactorService->verifyCode(
            $user->two_factor_secret,
            $request->code
        );

        if (!$valid) {
            return $this->error('Kode verifikasi salah.', null, 422);
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
        ]);

        return $this->success(null, '2FA berhasil dinonaktifkan');
    }
}

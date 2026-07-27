<?php

namespace App\Http\Controllers\Api;

use App\Models\UserSession;
use Illuminate\Http\JsonResponse;

class SessionController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $user = auth('api')->user();
        $sessions = UserSession::forUser($user->id)
            ->active()
            ->orderByDesc('is_main')
            ->orderByDesc('last_active_at')
            ->get()
            ->map(fn (UserSession $s) => $this->formatSession($s));

        return $this->success($sessions);
    }

    public function current(): JsonResponse
    {
        $session = $this->getCurrentSession();
        if (!$session) {
            return $this->error('Session tidak ditemukan', null, 404);
        }

        return $this->success($this->formatSession($session));
    }

    public function destroy(int $id): JsonResponse
    {
        $currentSession = $this->getCurrentSession();
        if (!$currentSession) {
            return $this->error('Session tidak ditemukan', null, 404);
        }

        if (!$currentSession->is_main) {
            return $this->error('Hanya main device yang bisa logout device lain.', null, 403);
        }

        $user = auth('api')->user();
        $session = UserSession::where('user_id', $user->id)->active()->find($id);

        if (!$session) {
            return $this->error('Session tidak ditemukan atau sudah inactive', null, 404);
        }

        if ($this->isCurrentSession($session)) {
            return $this->error('Tidak bisa logout session ini dari sini. Gunakan tombol logout.', null, 422);
        }

        $session->update(['is_active' => false]);

        return $this->success(null, 'Session berhasil dihapus');
    }

    public function destroyAll(): JsonResponse
    {
        $currentSession = $this->getCurrentSession();
        if (!$currentSession) {
            return $this->error('Session tidak ditemukan', null, 404);
        }

        if (!$currentSession->is_main) {
            return $this->error('Hanya main device yang bisa logout semua device lain.', null, 403);
        }

        $user = auth('api')->user();

        UserSession::forUser($user->id)
            ->active()
            ->where('id', '!=', $currentSession->id)
            ->update(['is_active' => false]);

        return $this->success(null, 'Semua session lain berhasil dihapus');
    }

    public function touch(): JsonResponse
    {
        $session = $this->getCurrentSession();
        if ($session) {
            $session->update(['last_active_at' => now()]);
        }
        return $this->success(['touched' => true]);
    }

    private function getCurrentSession(): ?UserSession
    {
        $fingerprintId = request()->header('X-Session-Fingerprint');
        if (!$fingerprintId) return null;

        $user = auth('api')->user();
        return UserSession::forUser($user->id)
            ->where('fingerprint_id', $fingerprintId)
            ->active()
            ->first();
    }

    private function isCurrentSession(UserSession $session): bool
    {
        $fingerprintId = request()->header('X-Session-Fingerprint');
        return $session->fingerprint_id === $fingerprintId;
    }

    private function formatSession(UserSession $s): array
    {
        $info = $s->device_info ?? [];
        $nested = $info['device_info'] ?? [];

        $screen = $info['screen'] ?? $nested['screen'] ?? null;
        $language = $info['language'] ?? $nested['language'] ?? null;
        $timezone = $info['timezone'] ?? $nested['timezone'] ?? null;

        return [
            'id' => $s->id,
            'device_name' => $s->device_name,
            'browser' => $s->browser,
            'os' => $s->os,
            'device_type' => $s->device_type,
            'ip_address' => $s->ip_address,
            'screen' => $screen,
            'language' => $language,
            'timezone' => $timezone,
            'user_agent' => $s->user_agent,
            'is_main' => $s->is_main,
            'is_current' => $this->isCurrentSession($s),
            'last_active_at' => $s->last_active_at?->toISOString(),
            'created_at' => $s->created_at->toISOString(),
        ];
    }
}

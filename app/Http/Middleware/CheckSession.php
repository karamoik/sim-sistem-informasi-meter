<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $fingerprintId = $request->header('X-Session-Fingerprint');

        if ($fingerprintId) {
            $user = $request->user();
            if ($user) {
                $session = UserSession::where('user_id', $user->id)
                    ->where('fingerprint_id', $fingerprintId)
                    ->active()
                    ->first();

                if (!$session) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Session telah ditutup dari device lain.',
                        'code' => 'session_killed',
                    ], 401);
                }

                if ($session->isExpired()) {
                    $session->update(['is_active' => false]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Session expired. Silakan login kembali.',
                        'code' => 'session_expired',
                    ], 401);
                }

                $lastActive = $session->last_active_at;
                if (!$lastActive || $lastActive->diffInSeconds(now()) > 60) {
                    $session->update(['last_active_at' => now()]);
                }
            }
        }

        return $next($request);
    }
}

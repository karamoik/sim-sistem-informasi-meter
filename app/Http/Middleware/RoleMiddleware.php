<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors'  => null,
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun anda telah dinonaktifkan. Hubungi admin.',
                'errors'  => null,
            ], 403);
        }

        if (!empty($roles) && !in_array($user->role, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke halaman ini.',
                'errors'  => null,
            ], 403);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnitScopeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role === 'admin') {
            return $next($request);
        }

        $assignedGiIds = $user->garduInduks->pluck('id')->toArray();

        if (empty($assignedGiIds)) {
            $request->merge(['_scope_gardu_induk_ids' => []]);
            return $next($request);
        }

        $request->merge(['_scope_gardu_induk_ids' => $assignedGiIds]);

        return $next($request);
    }
}

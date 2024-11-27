<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();
        $roleName = $user->getRole->name ?? null;

        if (!$roleName || !in_array($roleName, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }

        return $next($request);
    }
}

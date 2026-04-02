<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiRoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();
        // Manager can access all role-protected routes
        if (!$user || (!in_array($user->role, $roles) && $user->role !== 'manager')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::guard('admin')->user() ?? Auth::guard('employee')->user();
        
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            return redirect('/login');
        }

        $userRole = $user->role ?? ($user->isAdmin() ? 'admin' : null);

        // manager has same access as admin on admin routes — REMOVED
        // Managers now have their own /manager panel

        if (!in_array($userRole, $roles)) {
            abort(403, 'Unauthorized access to this panel.');
        }

        return $next($request);
    }
}

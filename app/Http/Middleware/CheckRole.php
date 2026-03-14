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
            return redirect('/login');
        }

        $userRole = $user->role ?? ($user->isAdmin() ? 'admin' : null);

        if (!in_array($userRole, $roles)) {
            abort(403, 'Unauthorized access to this panel.');
        }

        return $next($request);
    }
}

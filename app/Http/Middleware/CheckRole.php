<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect('/admin/login');
        }

        $userRole = auth()->user()->role;

        if (!in_array($userRole, $roles)) {
            abort(403, 'Unauthorized access to this panel.');
        }

        return $next($request);
    }
}

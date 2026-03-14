<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('super_admin')->check()) {
            return redirect('/superadmin/login');
        }

        return $next($request);
    }
}

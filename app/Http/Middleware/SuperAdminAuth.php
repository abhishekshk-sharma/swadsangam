<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Simple password protection (upgrade to proper auth later)
        if (session('super_admin_authenticated') !== true) {
            if ($request->path() !== 'superadmin/login' && !$request->is('superadmin/login')) {
                return redirect('/superadmin/login');
            }
        }
        
        return $next($request);
    }
}

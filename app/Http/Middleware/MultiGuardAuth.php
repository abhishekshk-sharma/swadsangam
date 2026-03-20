<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MultiGuardAuth
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('super_admin')->user() 
            ?? Auth::guard('admin')->user() 
            ?? Auth::guard('employee')->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            return redirect('/login');
        }

        return $next($request);
    }
}

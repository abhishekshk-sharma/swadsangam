<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdentifyBranch
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('admin')->user() ?? Auth::guard('employee')->user();

        if ($user && isset($user->branch_id) && $user->branch_id) {
            app()->instance('current_branch_id', (int) $user->branch_id);
        }

        return $next($request);
    }
}

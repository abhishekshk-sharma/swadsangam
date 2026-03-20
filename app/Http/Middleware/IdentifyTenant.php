<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('superadmin') || $request->is('superadmin/*')) {
            return $next($request);
        }

        $authUser = Auth::guard('admin')->user() ?? Auth::guard('employee')->user();

        if ($authUser && $authUser->tenant_id) {
            app()->instance('current_tenant_id', (int) $authUser->tenant_id);

            $tenant = Tenant::find($authUser->tenant_id);
            if ($tenant) {
                app()->instance('tenant', $tenant);
                view()->share('tenant', $tenant);
            }

            return $next($request);
        }

        // Guest path — login page, customer order page, etc.
        $host = $request->getHost();
        $slug = null;

        if (app()->environment('local') && $request->has('tenant')) {
            $slug = $request->get('tenant');
        } elseif (preg_match('/^(.+?)\./', $host, $matches)) {
            $candidate = $matches[1];
            if (!in_array($candidate, ['localhost', 'www', '127']) && !is_numeric($candidate)) {
                $slug = $candidate;
            }
        }

        $tenant = $slug ? Tenant::where('slug', $slug)->first() : null;

        if (!$tenant) {
            $tenant = Tenant::where('status', 'active')->first();
        }

        if (!$tenant) {
            if ($request->is('login') || $request->is('logout')) {
                return $next($request);
            }
            abort(404, 'Restaurant not found');
        }

        if ($tenant->status !== 'active') {
            abort(403, 'Restaurant account is suspended');
        }

        app()->instance('current_tenant_id', (int) $tenant->id);
        app()->instance('tenant', $tenant);
        view()->share('tenant', $tenant);

        return $next($request);
    }
}

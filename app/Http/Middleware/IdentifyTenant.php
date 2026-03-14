<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;
use App\Models\Admin;
use App\Models\Employee;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        // Super admin routes don't need tenant context
        if ($request->is('superadmin') || $request->is('superadmin/*')) {
            return $next($request);
        }

        // Check if a user is authenticated by reading the session ID directly,
        // then load the model WITHOUT global scope to avoid chicken-and-egg:
        // the scope needs current_tenant_id, but we're loading the user to GET it.
        $authUser = null;

        $adminSessionKey    = Auth::guard('admin')->getName();
        $employeeSessionKey = Auth::guard('employee')->getName();

        if ($adminId = session($adminSessionKey)) {
            $authUser = Admin::withoutGlobalScope('tenant')->find($adminId);
        } elseif ($empId = session($employeeSessionKey)) {
            $authUser = Employee::withoutGlobalScope('tenant')->find($empId);
        }

        if ($authUser && $authUser->tenant_id) {
            // Authenticated path — tenant is always the user's own tenant
            $tenantId = $authUser->tenant_id;
            session(['tenant_id' => $tenantId]);
            app()->instance('current_tenant_id', $tenantId);

            $tenant = Tenant::find($tenantId);
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

        session(['tenant_id' => $tenant->id]);
        app()->instance('current_tenant_id', $tenant->id);
        app()->instance('tenant', $tenant);
        view()->share('tenant', $tenant);

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        // Skip tenant identification for super admin and login routes
        if ($request->is('superadmin') || $request->is('superadmin/*') || $request->is('login') || $request->is('logout')) {
            return $next($request);
        }
        
        $host = $request->getHost();
        $slug = null;
        
        // For local development, use query parameter: ?tenant=demo
        if (app()->environment('local') && $request->has('tenant')) {
            $slug = $request->get('tenant');
        }
        // Extract subdomain: demo.yourapp.com -> demo
        elseif (preg_match('/^(.+?)\./', $host, $matches)) {
            $candidate = $matches[1];
            // Skip if it's localhost, www, 127, or an IP octet
            if (!in_array($candidate, ['localhost', 'www', '127']) && !is_numeric($candidate)) {
                $slug = $candidate;
            }
        }
        
        // Try by slug first, fall back to first active tenant
        $tenant = $slug ? Tenant::where('slug', $slug)->first() : null;
        
        if (!$tenant) {
            $tenant = Tenant::where('status', 'active')->first();
        }
        
        if (!$tenant) {
            abort(404, 'Restaurant not found');
        }
        
        if ($tenant->status !== 'active') {
            abort(403, 'Restaurant account is suspended');
        }
        
        // Store tenant in session
        session(['tenant_id' => $tenant->id]);
        
        // Make tenant available globally
        app()->instance('tenant', $tenant);
        view()->share('tenant', $tenant);
        
        return $next($request);
    }
}

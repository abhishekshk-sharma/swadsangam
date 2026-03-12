<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        // Skip tenant identification for super admin routes
        if ($request->is('superadmin') || $request->is('superadmin/*')) {
            return $next($request);
        }
        
        $host = $request->getHost();
        
        // For local development, use query parameter: ?tenant=demo
        if (app()->environment('local') && $request->has('tenant')) {
            $slug = $request->get('tenant');
        }
        // Extract subdomain: demo.yourapp.com -> demo
        elseif (preg_match('/^(.+?)\./', $host, $matches)) {
            $slug = $matches[1];
            // Skip if it's localhost or www
            if (in_array($slug, ['localhost', 'www', '127'])) {
                $slug = 'demo'; // default for local
            }
        } else {
            $slug = 'demo'; // default
        }
        
        $tenant = Tenant::where('slug', $slug)->first();
        
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

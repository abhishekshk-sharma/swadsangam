<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\Employee;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // withoutGlobalScope — no user is authenticated yet at this point
        $admin = Admin::withoutGlobalScope('tenant')
            ->where('email', $credentials['email'])
            ->where('is_active', true)
            ->first();

        if ($admin) {
            // Temporarily bind this admin's tenant so the global scope
            // doesn't filter out the admin during Auth::attempt()
            app()->instance('current_tenant_id', $admin->tenant_id);

            if (Auth::guard('admin')->attempt([
                'email'     => $credentials['email'],
                'password'  => $credentials['password'],
                'is_active' => true,
            ])) {
                $request->session()->regenerate();
                $tenantId = $admin->tenant_id;
                session(['tenant_id' => $tenantId]);
                app()->instance('current_tenant_id', $tenantId);
                return redirect('/admin/dashboard');
            }

            // Reset if attempt failed
            app()->forgetInstance('current_tenant_id');
        }

        $employee = Employee::withoutGlobalScope('tenant')
            ->where('email', $credentials['email'])
            ->where('is_active', true)
            ->first();

        if ($employee) {
            // Temporarily bind this employee's tenant so the global scope
            // doesn't filter out the employee during Auth::attempt()
            app()->instance('current_tenant_id', $employee->tenant_id);

            if (Auth::guard('employee')->attempt([
                'email'     => $credentials['email'],
                'password'  => $credentials['password'],
                'is_active' => true,
            ])) {
                $request->session()->regenerate();
                $employee  = Auth::guard('employee')->user();
                $tenantId  = $employee->tenant_id;
                session(['tenant_id' => $tenantId]);
                app()->instance('current_tenant_id', $tenantId);
        
                if ($employee->isManager()) return redirect('/manager/dashboard');
                if ($employee->isWaiter())  return redirect('/waiter/dashboard');
                if ($employee->isChef())    return redirect('/cook/dashboard');
                if ($employee->isCashier()) return redirect('/cashier/dashboard');
            }

            // Reset if attempt failed
            app()->forgetInstance('current_tenant_id');
        }

        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        Auth::guard('employee')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('login');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\Employee;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        $request->session()->forget('errors');
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required',
        ]);

        $loginVal = $request->login;
        $isEmail  = filter_var($loginVal, FILTER_VALIDATE_EMAIL);

        // Try Admin — by phone first, then email fallback
        if ($isEmail) {
            $admin = Admin::withoutGlobalScope('tenant')
                ->where('email', $loginVal)
                ->where('is_active', true)
                ->first();
        } else {
            $admin = Admin::withoutGlobalScope('tenant')
                ->where('phone', $loginVal)
                ->where('is_active', true)
                ->first();
        }

        if ($admin) {
            app()->instance('current_tenant_id', $admin->tenant_id);

            if (\Illuminate\Support\Facades\Hash::check($request->password, $admin->password)) {
                Auth::guard('admin')->login($admin);
                $request->session()->regenerate();
                session(['tenant_id' => $admin->tenant_id]);
                app()->instance('current_tenant_id', $admin->tenant_id);
                return redirect('/admin/dashboard');
            }

            app()->forgetInstance('current_tenant_id');
        }

        // Try Employee — by phone first, then email fallback
        $employee = Employee::withoutGlobalScope('tenant')
            ->where('is_active', true)
            ->where(function ($q) use ($loginVal, $isEmail) {
                $q->where('phone', $loginVal);
                if ($isEmail) $q->orWhere('email', $loginVal);
            })
            ->first();

        if ($employee) {
            app()->instance('current_tenant_id', $employee->tenant_id);

            // Auth::attempt only works with the model's username field (email by default)
            // So we manually verify password and log in
            if (\Illuminate\Support\Facades\Hash::check($request->password, $employee->password)) {
                Auth::guard('employee')->login($employee);
                $request->session()->regenerate();
                session(['tenant_id' => $employee->tenant_id]);
                app()->instance('current_tenant_id', $employee->tenant_id);

                if ($employee->isManager()) return redirect('/manager/dashboard');
                if ($employee->isWaiter())  return redirect('/waiter/dashboard');
                if ($employee->isChef())    return redirect('/cook/dashboard');
                if ($employee->isCashier()) return redirect('/cashier/dashboard');
            }

            app()->forgetInstance('current_tenant_id');
        }

        return back()->withErrors(['login' => 'Invalid phone/email or password.'])->withInput();
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

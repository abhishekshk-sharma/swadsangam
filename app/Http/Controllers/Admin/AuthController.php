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

        // Try Admin — match phone or email
        $admin = Admin::withoutGlobalScope('tenant')
            ->where('is_active', true)
            ->where(fn($q) => $q->where('phone', $loginVal)->orWhere('email', $loginVal))
            ->first();

        if ($admin && \Illuminate\Support\Facades\Hash::check($request->password, $admin->password)) {
            app()->instance('current_tenant_id', $admin->tenant_id);
            Auth::guard('admin')->login($admin);
            $request->session()->regenerate();
            session(['tenant_id' => $admin->tenant_id]);
            return redirect('/admin/dashboard');
        }

        // Try Employee — match phone or email
        $employee = Employee::withoutGlobalScope('tenant')
            ->where('is_active', true)
            ->where(fn($q) => $q->where('phone', $loginVal)->orWhere('email', $loginVal))
            ->first();

        if ($employee && \Illuminate\Support\Facades\Hash::check($request->password, $employee->password)) {
            app()->instance('current_tenant_id', $employee->tenant_id);
            Auth::guard('employee')->login($employee);
            $request->session()->regenerate();
            session(['tenant_id' => $employee->tenant_id]);
            app()->instance('current_tenant_id', $employee->tenant_id);

            if ($employee->isManager()) return redirect('/manager/dashboard');
            if ($employee->isWaiter())  return redirect('/waiter/dashboard');
            if ($employee->isChef())    return redirect('/cook/dashboard');
            if ($employee->isCashier()) return redirect('/cashier/dashboard');
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

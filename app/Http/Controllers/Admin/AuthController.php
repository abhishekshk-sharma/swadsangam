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
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Find admin by email first
        $admin = Admin::where('email', $credentials['email'])->where('is_active', true)->first();
        if ($admin) {
            // Set correct tenant in session
            session(['tenant_id' => $admin->tenant_id]);
            
            if (Auth::guard('admin')->attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'is_active' => true])) {
                $request->session()->regenerate();
                return redirect('/admin/dashboard');
            }
        }
        
        // Find employee by email
        $employee = Employee::where('email', $credentials['email'])->where('is_active', true)->first();
        if ($employee) {
            // Set correct tenant in session
            session(['tenant_id' => $employee->tenant_id]);
            
            if (Auth::guard('employee')->attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'is_active' => true])) {
                $request->session()->regenerate();
                $employee = Auth::guard('employee')->user();
                
                if ($employee->isWaiter()) {
                    return redirect('/waiter/dashboard');
                }
                if ($employee->isChef()) {
                    return redirect('/cook/dashboard');
                }
                if ($employee->isCashier()) {
                    return redirect('/cashier/dashboard');
                }
            }
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

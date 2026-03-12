<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        $tenantId = session('tenant_id');
        
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'tenant_id' => $tenantId, 'is_active' => true])) {
            $request->session()->regenerate();
            
            // Redirect based on role
            if (auth()->user()->role === 'waiter') {
                return redirect('/waiter/dashboard');
            }
            
            if (auth()->user()->role === 'chef') {
                return redirect('/cook/dashboard');
            }
            
            if (auth()->user()->role === 'cashier') {
                return redirect('/cashier/dashboard');
            }
            if (auth()->user()->role === 'admin') {
                return redirect('/admin/dashboard');
            }
            
            // return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('login');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user = current_user();
        $layout = $this->getLayout($user);
        
        return view('profile.show', compact('user', 'layout'));
    }

    public function edit()
    {
        $user = current_user();
        
        // Only admin, manager, super_admin can edit
        if ($user instanceof \App\Models\Employee && !in_array($user->role, ['admin', 'manager', 'super_admin'])) {
            abort(403, 'You cannot edit your profile.');
        }
        
        $layout = $this->getLayout($user);
        
        return view('profile.edit', compact('user', 'layout'));
    }

    public function update(Request $request)
    {
        $user = current_user();
        
        // Only admin, manager, super_admin can update
        if ($user instanceof \App\Models\Employee && !in_array($user->role, ['admin', 'manager', 'super_admin'])) {
            abort(403, 'You cannot update your profile.');
        }

        $table = match(true) {
            $user instanceof \App\Models\Admin       => 'admins',
            $user instanceof \App\Models\Employee    => 'employees',
            $user instanceof \App\Models\SuperAdmin  => 'super_admins',
            default => 'admins',
        };

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:' . $table . ',email,' . $user->id,
            'phone'    => 'nullable|string|max:15',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
    }

    protected function getLayout($user)
    {
        if ($user instanceof \App\Models\Admin || $user instanceof \App\Models\SuperAdmin) {
            return 'layouts.admin';
        }
        return match($user->role ?? '') {
            'waiter'  => 'layouts.waiter',
            'chef'    => 'layouts.cook',
            'cashier' => 'layouts.cashier',
            default   => 'layouts.admin',
        };
    }
}

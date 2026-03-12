<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $layout = $this->getLayout($user->role);
        
        return view('profile.show', compact('user', 'layout'));
    }

    public function edit()
    {
        $user = auth()->user();
        
        // Only admin, manager, super_admin can edit
        if (!in_array($user->role, ['admin', 'manager', 'super_admin'])) {
            abort(403, 'You cannot edit your profile.');
        }
        
        $layout = $this->getLayout($user->role);
        
        return view('profile.edit', compact('user', 'layout'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        // Only admin, manager, super_admin can update
        if (!in_array($user->role, ['admin', 'manager', 'super_admin'])) {
            abort(403, 'You cannot update your profile.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:15',
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

    protected function getLayout($role)
    {
        return match($role) {
            'waiter' => 'layouts.waiter',
            'chef' => 'layouts.cook',
            'cashier' => 'layouts.cashier',
            default => 'layouts.admin',
        };
    }
}

<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SuperAdminProfileController extends Controller
{
    private function me(): SuperAdmin
    {
        return Auth::guard('super_admin')->user();
    }

    public function index()
    {
        $me         = $this->me();
        $superAdmins = SuperAdmin::latest()->get();
        return view('superadmin.profile.index', compact('me', 'superAdmins'));
    }

    // Update current super admin's own profile
    public function updateProfile(Request $request)
    {
        $me = $this->me();

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:super_admins,email,' . $me->id,
            'password' => 'nullable|min:6|confirmed',
        ]);

        $data = $request->only(['name', 'email']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $me->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    // Update any super admin from the list
    public function update(Request $request, $id)
    {
        $user = SuperAdmin::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:super_admins,email,' . $id,
            'password' => 'nullable|min:6',
        ]);

        $data = $request->only(['name', 'email']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', $user->name . '\'s profile updated.');
    }

    // Add new super admin
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:super_admins,email',
            'password' => 'required|min:6',
        ]);

        SuperAdmin::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'is_active' => true,
        ]);

        return back()->with('success', 'New Super Admin added.');
    }

    // Delete a super admin (cannot delete yourself)
    public function destroy($id)
    {
        $me = $this->me();

        if ($me->id == $id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        SuperAdmin::findOrFail($id)->delete();

        return back()->with('success', 'Super Admin removed.');
    }
}

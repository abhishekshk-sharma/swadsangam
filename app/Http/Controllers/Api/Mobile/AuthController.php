<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $loginVal = $request->login;

        $employee = Employee::withoutGlobalScopes()
            ->where('is_active', true)
            ->where(fn($q) => $q->where('phone', $loginVal)->orWhere('email', $loginVal))
            ->first();

        if (!$employee || !Hash::check($request->password, $employee->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if (!in_array($employee->role, ['waiter', 'chef', 'cashier', 'manager'])) {
            return response()->json(['message' => 'Access denied for this role.'], 403);
        }

        $employee->tokens()->where('name', 'mobile')->delete();
        $token = $employee->createToken('mobile')->plainTextToken;

        return response()->json([
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => $this->formatUser($employee),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function updateFcmToken(Request $request)
    {
        // FCM disabled — polling only.
        return response()->json(['message' => 'Polling mode active. FCM not used.']);
    }

    public function profile(Request $request)
    {
        return response()->json($this->formatUser($request->user()));
    }

    public function refresh(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token'      => $token,
            'token_type' => 'Bearer',
        ]);
    }

    private function formatUser(Employee $employee): array
    {
        $tenant = \App\Models\Tenant::with('gstSlab')->find($employee->tenant_id);
        $branch = $employee->branch_id
            ? \App\Models\Branch::with('gstSlab')->find($employee->branch_id)
            : null;

        return [
            'id'          => $employee->id,
            'name'        => $employee->name,
            'email'       => $employee->email,
            'role'        => $employee->role,
            'tenant_id'   => $employee->tenant_id,
            'tenant_name' => $tenant?->name ?? '',
            'branch_id'   => $employee->branch_id,
            'branch_name' => $branch?->name ?? '',
            'gst'         => $this->resolveGst($branch, $tenant),
        ];
    }

    private function resolveGst($branch, $tenant): array
    {
        $slab = $branch?->gstSlab ?? $tenant?->gstSlab;
        $mode = ($branch?->gst_slab_id ? $branch->gst_mode : null)
             ?? ($tenant?->gst_slab_id ? $tenant->gst_mode : null);

        if (!$slab || !$mode) {
            return ['enabled' => false];
        }

        return [
            'enabled'   => true,
            'mode'      => $mode,
            'slab_name' => $slab->name,
            'cgst_pct'  => (float) $slab->cgst_rate,
            'sgst_pct'  => (float) $slab->sgst_rate,
            'total_pct' => (float) ($slab->cgst_rate + $slab->sgst_rate),
        ];
    }
}

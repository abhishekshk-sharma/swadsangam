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
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $employee = Employee::withoutGlobalScopes()
            ->where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (!$employee || !Hash::check($request->password, $employee->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if (!in_array($employee->role, ['waiter', 'chef', 'cashier'])) {
            return response()->json(['message' => 'Access denied for this role.'], 403);
        }

        // Revoke previous mobile tokens for this device
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
        return [
            'id'        => $employee->id,
            'name'      => $employee->name,
            'email'     => $employee->email,
            'role'      => $employee->role,
            'tenant_id' => $employee->tenant_id,
            'branch_id' => $employee->branch_id,
        ];
    }
}

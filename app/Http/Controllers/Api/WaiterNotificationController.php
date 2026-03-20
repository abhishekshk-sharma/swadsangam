<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class WaiterNotificationController extends Controller
{
    public function getUpdates(Request $request)
    {
        $user      = auth()->guard('employee')->user();
        $tenantId  = $user?->tenant_id;
        $branchId  = $user?->branch_id;
        $lastCheck = $request->input('last_check', now()->subMinutes(5)->toISOString());

        $branchScope = function ($q) use ($branchId) {
            if ($branchId) {
                $q->where('branch_id', $branchId);
            } else {
                $q->whereNull('branch_id');
            }
        };

        $readyOrders = Order::with('table')
            ->where('tenant_id', $tenantId)
            ->where('status', 'ready')
            ->where('updated_at', '>', $lastCheck)
            ->whereDate('created_at', today())
            ->where($branchScope)
            ->get()
            ->map(fn($order) => [
                'id'           => $order->id,
                'table_number' => $order->table?->table_number,
                'total_amount' => $order->total_amount,
            ]);

        $reactivatedOrders = Order::where('tenant_id', $tenantId)
            ->where('status', 'preparing')
            ->where('updated_at', '>', $lastCheck)
            ->whereDate('created_at', today())
            ->where($branchScope)
            ->pluck('id');

        return response()->json([
            'ready_orders' => $readyOrders,
            'reactivated_orders' => $reactivatedOrders,
            'timestamp' => now()->toISOString()
        ]);
    }
}

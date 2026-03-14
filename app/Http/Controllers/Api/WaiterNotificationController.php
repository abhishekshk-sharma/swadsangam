<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class WaiterNotificationController extends Controller
{
    public function getUpdates(Request $request)
    {
        $lastCheck = $request->input('last_check', now()->subMinutes(5)->toISOString());
        $tenantId = session('tenant_id');

        $readyOrders = Order::with('table')
            ->where('tenant_id', $tenantId)
            ->where('status', 'ready')
            ->where('updated_at', '>', $lastCheck)
            ->whereDate('created_at', today())
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'table_number' => $order->table->table_number,
                'total_amount' => $order->total_amount
            ]);

        // Orders that went back to preparing (new items added) — remove from notified set on client
        $reactivatedOrders = Order::where('tenant_id', $tenantId)
            ->where('status', 'preparing')
            ->where('updated_at', '>', $lastCheck)
            ->whereDate('created_at', today())
            ->pluck('id');

        return response()->json([
            'ready_orders' => $readyOrders,
            'reactivated_orders' => $reactivatedOrders,
            'timestamp' => now()->toISOString()
        ]);
    }
}

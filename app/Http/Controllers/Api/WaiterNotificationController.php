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
        
        // Get orders that became ready after last check
        $readyOrders = Order::with('table')
            ->where('tenant_id', $tenantId)
            ->where('status', 'ready')
            ->where('updated_at', '>', $lastCheck)
            ->whereDate('created_at', today())
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'table_number' => $order->table->table_number,
                    'total_amount' => $order->total_amount
                ];
            });

        return response()->json([
            'ready_orders' => $readyOrders,
            'timestamp' => now()->toISOString()
        ]);
    }
}

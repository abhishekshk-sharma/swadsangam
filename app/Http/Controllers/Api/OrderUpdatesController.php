<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderUpdatesController extends Controller
{
    public function getUpdates(Request $request)
    {
        $lastCheck = $request->input('last_check');
        
        $newOrders = Order::where('tenant_id', session('tenant_id'))
            ->where('created_at', '>', $lastCheck)
            ->with('table', 'orderItems.menuItem')
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'table_name' => 'Table ' . $order->table->table_number,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'items_count' => $order->orderItems->count(),
                    'created_at' => $order->created_at->format('h:i A'),
                    'type' => 'new_order'
                ];
            });

        $updatedOrders = Order::where('tenant_id', session('tenant_id'))
            ->where('updated_at', '>', $lastCheck)
            ->where('created_at', '<=', $lastCheck)
            ->with('table', 'orderItems.menuItem')
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'table_name' => 'Table ' . $order->table->table_number,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'type' => 'status_update'
                ];
            });

        return response()->json([
            'new_orders' => $newOrders,
            'updated_orders' => $updatedOrders,
            'timestamp' => now()->toIso8601String()
        ]);
    }
}

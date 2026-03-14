<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderUpdatesController extends Controller
{
    public function getUpdates(Request $request)
    {
        $tenantId = session('tenant_id');
        $panel    = $request->query('panel', 'all');

        $query = Order::with(['table', 'orderItems.menuItem'])
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', today());

        // Scope to only statuses each panel cares about
        match ($panel) {
            'cook'    => $query->whereIn('status', ['pending', 'preparing']),
            'cashier' => $query->whereIn('status', ['served']),
            'waiter'  => $query->whereNotIn('status', ['paid']),
            default   => null, // admin gets all
        };

        $orders = $query->get()->map(fn($order) => [
            'id'             => $order->id,
            'status'         => $order->status,
            'total_amount'   => (float) $order->total_amount,
            'table_number'   => $order->table->table_number,
            'created_at'     => $order->created_at->format('h:i A'),
            'customer_notes' => $order->customer_notes,
            'items'          => $order->orderItems->map(fn($item) => [
                'id'       => $item->id,
                'status'   => $item->status,
                'name'     => $item->menuItem->name,
                'quantity' => $item->quantity,
                'price'    => (float) $item->price,
                'notes'    => $item->notes,
            ])->values(),
        ]);

        return response()->json(['orders' => $orders]);
    }
}

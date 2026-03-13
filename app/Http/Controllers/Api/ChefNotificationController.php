<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Order, OrderItem};
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ChefNotificationController extends Controller
{
    public function getUpdates(Request $request)
    {
        $lastCheck = $request->input('last_check', now()->subMinutes(5)->toISOString());
        $tenantId = session('tenant_id');
        
        $lastCheckTime = Carbon::parse($lastCheck);
        
        // Get all pending order items created or updated after last check
        $newPendingItems = OrderItem::with(['order.table', 'menuItem'])
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->where('created_at', '>', $lastCheckTime)
            ->whereHas('order', function($query) {
                $query->whereDate('created_at', today())
                      ->where('status', '!=', 'paid');
            })
            ->get();
        
        // Group by order_id to identify new orders vs additional items
        $groupedItems = $newPendingItems->groupBy('order_id');
        
        $newOrders = [];
        $additionalItems = [];
        
        foreach ($groupedItems as $orderId => $items) {
            $order = $items->first()->order;
            $orderCreatedAt = Carbon::parse($order->created_at);
            
            // If order was created after last check, it's a new order
            if ($orderCreatedAt->gt($lastCheckTime)) {
                $newOrders[] = [
                    'id' => $order->id,
                    'table_number' => $order->table->table_number,
                    'items_count' => $items->count(),
                    'total_items_count' => $order->orderItems()->count(),
                    'created_at' => $order->created_at->toISOString(),
                    'items' => $items->map(fn($item) => [
                        'id' => $item->id,
                        'name' => $item->menuItem->name,
                        'quantity' => $item->quantity,
                        'status' => $item->status
                    ])->toArray()
                ];
            } else {
                // Order existed before, these are additional items
                $additionalItems[] = [
                    'id' => $order->id,
                    'table_number' => $order->table->table_number,
                    'new_items_count' => $items->count(),
                    'total_items_count' => $order->orderItems()->count(),
                    'items_created_at' => $items->max('created_at')->toISOString(),
                    'items' => $items->map(fn($item) => [
                        'id' => $item->id,
                        'name' => $item->menuItem->name,
                        'quantity' => $item->quantity,
                        'status' => $item->status
                    ])->toArray()
                ];
            }
        }

        Log::info('Chef updates', [
            'tenant_id' => $tenantId,
            'last_check' => $lastCheck,
            'new_orders_count' => count($newOrders),
            'additional_items_count' => count($additionalItems),
            'total_pending_items' => $newPendingItems->count()
        ]);

        return response()->json([
            'new_orders' => $newOrders,
            'additional_items' => $additionalItems,
            'timestamp' => now()->toISOString()
        ]);
    }
}

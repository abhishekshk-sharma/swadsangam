<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Events\NewOrder;

class OrderController extends Controller
{
    public function showMenu($qrCode)
    {
        $table = RestaurantTable::where('qr_code', $qrCode)->firstOrFail();

        $menuItems = MenuItem::with('menuCategory')
            ->where('is_available', true)
            ->get()
            ->groupBy(fn($i) => $i->menuCategory?->sort_order . '||' . ($i->menuCategory?->name ?? 'Other'))
            ->sortKeys()
            ->mapWithKeys(fn($items, $key) => [
                explode('||', $key)[1] => $items
            ]);

        $activeOrders = Order::where('table_id', $table->id)
            ->whereIn('status', ['pending', 'preparing'])
            ->with('items.menuItem')
            ->get();

        return view('customer.menu', compact('table', 'menuItems', 'activeOrders'));
    }

    public function placeOrder(Request $request, $qrCode)
    {
        try {
            $table = RestaurantTable::where('qr_code', $qrCode)->firstOrFail();
            $request->validate(['items' => 'required|array']);

            $totalAmount = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $menuItem = MenuItem::findOrFail($item['id']);
                $totalAmount += $menuItem->price * $item['quantity'];
                $orderItems[] = [
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $item['quantity'],
                    'price' => $menuItem->price
                ];
            }

            $order = Order::create([
                'table_id' => $table->id,
                'total_amount' => $totalAmount,
                'status' => 'pending'
            ]);

            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }

            broadcast(new NewOrder($order->load('items.menuItem', 'table')))->toOthers();

            return response()->json(['success' => true, 'order' => $order]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getOrderStatus($orderId)
    {
        $order = Order::with('items.menuItem')->findOrFail($orderId);
        return response()->json($order);
    }
}

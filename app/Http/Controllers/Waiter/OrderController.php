<?php

namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use App\Models\{Order, OrderItem, RestaurantTable, MenuItem, User};
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('table', 'items.menuItem')
            ->whereDate('created_at', today())
            ->where('status', '!=', 'paid')
            ->latest()
            ->get();

        $menuItems = MenuItem::where('is_available', true)->get();

        return view('waiter.orders.index', compact('orders', 'menuItems'));
    }

    public function create()
    {
        $tables = RestaurantTable::where('is_occupied', false)->get();
        $menuItems = MenuItem::where('is_available', true)->get();

        return view('waiter.orders.create', compact('tables', 'menuItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'table_id' => 'required|exists:restaurant_tables,id',
            'items' => 'required|array',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $total = 0;
        foreach ($request->items as $item) {
            $menuItem = MenuItem::find($item['menu_item_id']);
            $total += $menuItem->price * $item['quantity'];
        }

        $order = Order::create([
            'tenant_id' => session('tenant_id'),
            'table_id' => $request->table_id,
            'user_id' => auth()->id(),
            'status' => 'pending',
            'total_amount' => $total,
        ]);

        foreach ($request->items as $item) {
            $menuItem = MenuItem::find($item['menu_item_id']);
            OrderItem::create([
                'tenant_id' => session('tenant_id'),
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'quantity' => $item['quantity'],
                'price' => $menuItem->price,
                'status' => 'pending',
            ]);
        }

        RestaurantTable::find($request->table_id)->update(['is_occupied' => true]);

        event(new \App\Events\OrderCreated($order));
        $this->notifyChefs($order);

        return redirect('/waiter/orders')->with('success', 'Order created successfully');
    }

    public function markServed($id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => 'served']);
        
        event(new \App\Events\OrderStatusUpdated($order, 'ready'));
        
        return response()->json(['success' => true]);
    }

    protected function notifyChefs($order, $specificItems = null)
    {
        $chefs = User::where('tenant_id', session('tenant_id'))
            ->where('role', 'chef')
            ->where('is_active', true)
            ->whereNotNull('telegram_chat_id')
            ->get();

        $telegram = new \App\Services\TelegramService();
        
        $itemsToNotify = $specificItems ?? $order->items;
        
        $orderData = [
            'order_id' => $order->id,
            'table_name' => 'Table ' . $order->table->table_number,
            'time' => now()->format('h:i A'),
            'items' => collect($itemsToNotify)->map(fn($item) => [
                'name' => $item->menuItem->name,
                'quantity' => $item->quantity
            ])->toArray(),
            'total' => $order->total_amount,
            'is_additional' => $specificItems !== null
        ];

        foreach ($chefs as $chef) {
            $telegram->sendOrderNotification($chef->telegram_chat_id, $orderData);
        }
    }

    public function addItems(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        if ($order->payment_status === 'paid') {
            return redirect()->back()->with('error', 'Cannot add items to paid order');
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $additionalTotal = 0;
        $newItems = [];
        
        foreach ($request->items as $item) {
            $menuItem = MenuItem::find($item['menu_item_id']);
            $additionalTotal += $menuItem->price * $item['quantity'];
            
            $orderItem = OrderItem::create([
                'tenant_id' => session('tenant_id'),
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'quantity' => $item['quantity'],
                'price' => $menuItem->price,
                'status' => 'pending',
            ]);
            
            $newItems[] = $orderItem;
        }

        $order->update([
            'total_amount' => $order->total_amount + $additionalTotal
        ]);

        $order->refresh();
        $this->notifyChefs($order, $newItems);

        return redirect('/waiter/orders')->with('success', 'Items added successfully');
    }
}

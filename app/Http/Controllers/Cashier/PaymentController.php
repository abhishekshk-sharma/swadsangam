<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $orders = Order::with(['table', 'orderItems.menuItem'])
            ->where('status', 'served')
            ->latest()
            ->get();

        return view('cashier.payments.index', compact('orders'));
    }

    public function processPayment(Request $request, Order $order)
    {
        $request->validate([
            'payment_mode' => 'required|in:cash,upi,card',
            'cash_received' => 'nullable|numeric|min:0'
        ]);

        $order->update([
            'status' => 'paid',
            'payment_mode' => $request->payment_mode,
            'paid_at' => now()
        ]);

        $order->table->update(['is_occupied' => false]);

        event(new \App\Events\OrderStatusUpdated($order, 'served'));

        return back()->with('success', 'Payment received! Order closed.');
    }

    public function history()
    {
        $orders = Order::with(['table', 'orderItems.menuItem'])
            ->where('status', 'paid')
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        return view('cashier.payments.history', compact('orders'));
    }
}

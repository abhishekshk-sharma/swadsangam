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
        // Global scope already ensures this order belongs to current tenant
        // Extra guard in case route model binding is bypassed
        abort_if($order->tenant_id !== (int) $this->currentTenantId(), 403);

        $request->validate([
            'payment_mode'  => 'required|in:cash,upi,card',
            'cash_received' => 'nullable|numeric|min:0',
        ]);

        $order->update([
            'status'       => 'paid',
            'payment_mode' => $request->payment_mode,
            'paid_at'      => now(),
        ]);

        $order->table->update(['is_occupied' => false]);

        event(new \App\Events\OrderStatusUpdated($order, 'served'));

        return redirect()->route('cashier.payments.index', ['paid_order' => $order->id])
            ->with('success', 'Payment received! Order closed.');
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

    private function currentTenantId(): ?int
    {
        $user = \Illuminate\Support\Facades\Auth::guard('employee')->user()
            ?? \Illuminate\Support\Facades\Auth::guard('admin')->user();
        return $user ? (int) $user->tenant_id : (int) session('tenant_id');
    }
}

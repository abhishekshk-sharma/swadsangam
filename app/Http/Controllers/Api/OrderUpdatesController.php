<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderUpdatesController extends Controller
{
    public function getUpdates(Request $request)
    {
        $panel    = $request->query('panel', 'all');
        $user     = Auth::guard('employee')->user() ?? Auth::guard('admin')->user();
        $branchId = $user?->branch_id ?? null;

        $query = Order::with(['table.category', 'branch', 'user', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()])])
            ->whereDate('created_at', today())
            ->where(function ($q) use ($branchId) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                } else {
                    $q->whereNull('branch_id');
                }
            });

        match ($panel) {
            'cook'            => $query->whereIn('status', ['pending', 'preparing', 'ready', 'served']),
            'cashier'         => $query->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('is_parcel', false)->whereIn('status', ['served', 'checkout']);
                })
                ->orWhere(function ($q2) {
                    $q2->where('is_parcel', true)->where('status', 'ready');
                });
            }),
            'cashier_parcels' => $query->where('is_parcel', true)
                                       ->whereNotIn('status', ['paid', 'cancelled']),
            'waiter'          => $query->whereNotIn('status', ['paid', 'checkout', 'cancelled']),
            'admin_waiter'    => null, // handled separately below
            default           => null,
        };

        // admin_waiter panel: return both active orders + payment orders
        if ($panel === 'admin_waiter') {
            // For admin_waiter, branch filter comes from explicit query param (session-selected branch)
            // not from the user's own branch_id
            $adminBranchId = $request->filled('branch_id') ? (int) $request->branch_id : null;

            $baseQuery = Order::with(['table.category', 'branch', 'user', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()])])
                ->whereDate('created_at', today())
                ->when(
                    $adminBranchId,
                    fn($q) => $q->where('branch_id', $adminBranchId),
                    fn($q) => $q  // no branch filter = all branches ("All Branches" selected)
                );

            $activeOrders = (clone $baseQuery)
                ->whereNotIn('status', ['paid', 'checkout', 'cancelled'])
                ->latest()->get();

            $paymentOrders = (clone $baseQuery)
                ->where(function ($q) {
                    $q->where(fn($q2) => $q2->where('is_parcel', false)->whereIn('status', ['served', 'checkout']))
                      ->orWhere(fn($q2) => $q2->where('is_parcel', true)->where('status', 'ready'));
                })
                ->latest()->get();

            $mapOrder = fn($order) => [
                'id'             => $order->id,
                'status'         => $order->status,
                'is_parcel'      => (bool) $order->is_parcel,
                'total_amount'   => (float) $order->total_amount,
                'table_number'   => $order->table?->table_number,
                'table_category' => $order->table?->category?->name,
                'branch_name'    => $order->branch?->name,
                'created_at'     => $order->created_at->format('h:i A'),
                'created_at_ts'  => $order->created_at->timestamp,
                'customer_notes' => $order->customer_notes,
                'user_name'      => $order->user?->name,
                'items'          => $order->orderItems->map(fn($item) => [
                    'id'       => $item->id,
                    'status'   => $item->status,
                    'name'     => $item->menuItem?->name ?? '[Deleted Item]',
                    'quantity' => $item->quantity,
                    'price'    => (float) $item->price,
                    'notes'    => $item->notes,
                ])->values(),
            ];

            return response()->json([
                'orders'          => $activeOrders->map($mapOrder)->values(),
                'payment_orders'  => $paymentOrders->map($mapOrder)->values(),
                'current_user_id' => $user?->id,
            ]);
        }

        // Resolve current authenticated user across all guards
        $currentUser = $user;

        $orders = $query->get()->map(fn($order) => [
            'id'             => $order->id,
            'status'         => $order->status,
            'is_parcel'      => (bool) $order->is_parcel,
            'total_amount'   => (float) $order->total_amount,
            'table_number'   => $order->table?->table_number,
            'created_at'     => $order->created_at->format('h:i A'),
            'created_at_ts'  => $order->created_at->timestamp,
            'customer_notes' => $order->customer_notes,
            'created_by_id'  => $order->user_id,
            'items'          => $order->orderItems->map(fn($item) => [
                'id'       => $item->id,
                'status'   => $item->status,
                'name'     => $item->menuItem?->name ?? '[Deleted Item]',
                'quantity' => $item->quantity,
                'price'    => (float) $item->price,
                'notes'    => $item->notes,
            ])->values(),
        ]);

        return response()->json([
            'orders'          => $orders,
            'current_user_id' => $currentUser?->id,
        ]);
    }
}

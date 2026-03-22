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

        $query = Order::with(['table', 'orderItems.menuItem'])
            ->whereDate('created_at', today())
            ->where(function ($q) use ($branchId) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                } else {
                    $q->whereNull('branch_id');
                }
            });

        match ($panel) {
            'cook'            => $query->whereIn('status', ['pending', 'preparing']),
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
            'waiter'          => $query->whereNotIn('status', ['paid']),
            default           => null,
        };

        // Resolve current authenticated user across all guards
        $currentUser = $user;

        $orders = $query->get()->map(fn($order) => [
            'id'             => $order->id,
            'status'         => $order->status,
            'is_parcel'      => (bool) $order->is_parcel,
            'total_amount'   => (float) $order->total_amount,
            'table_number'   => $order->table?->table_number,
            'created_at'     => $order->created_at->format('h:i A'),
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

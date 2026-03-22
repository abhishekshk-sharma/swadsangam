<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function poll(Request $request)
    {
        $employee = $request->user();
        $tenantId = (int) $employee->tenant_id;
        $branchId = $employee->branch_id ?? null;
        $panel    = $request->query('panel', 'waiter');

        $query = Order::with(['table.category', 'orderItems.menuItem'])
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->where(function ($q) use ($branchId) {
                $branchId
                    ? $q->where('branch_id', $branchId)
                    : $q->whereNull('branch_id');
            });

        match ($panel) {
            'chef' => $query->whereIn('status', ['pending', 'preparing']),

            'cashier' => $query->where(function ($q) {
                $q->where(fn($q2) => $q2->where('is_parcel', false)
                                        ->whereIn('status', ['served', 'checkout']))
                  ->orWhere(fn($q2) => $q2->where('is_parcel', true)
                                          ->where('status', 'ready'));
            }),

            'cashier_parcels' => $query->where('is_parcel', true)
                                       ->whereNotIn('status', ['paid', 'cancelled']),

            default => // waiter
                $query->whereNotIn('status', ['paid', 'checkout', 'cancelled']),
        };

        $orders = $query->latest()->get()->map(fn($order) => [
            'id'             => $order->id,
            'status'         => $order->status,
            'is_parcel'      => (bool) $order->is_parcel,
            'total_amount'   => (float) $order->total_amount,
            'customer_notes' => $order->customer_notes,
            'payment_mode'   => $order->payment_mode,
            'created_at'     => $order->created_at,
            'created_at_ts'  => $order->created_at->timestamp,
            'table'          => $order->table ? [
                'id'           => $order->table->id,
                'table_number' => $order->table->table_number,
                'category'     => $order->table->category?->name,
            ] : null,
            'items' => $order->orderItems->map(fn($i) => [
                'id'       => $i->id,
                'name'     => $i->menuItem?->name ?? '[Deleted]',
                'quantity' => $i->quantity,
                'price'    => (float) $i->price,
                'status'   => $i->status,
                'notes'    => $i->notes,
            ])->values(),
        ]);

        return response()->json(['orders' => $orders]);
    }
}

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
        $panel = $request->query('panel', 'waiter');

        // Resolve authenticated user across all guards
        $user = Auth::guard('employee')->user()
             ?? Auth::guard('admin')->user()
             ?? Auth::guard('super_admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $tenantId = (int) $user->tenant_id;
        $branchId = $user->branch_id ?? null;

        // admin_waiter: branch comes from query param (branch selector on page), not user's branch
        // if ($panel === 'admin_waiter') {
        //     $adminBranchId = $request->filled('branch_id') ? (int) $request->branch_id : null;

        //     $base = $this->baseQuery($tenantId)
        //         ->when($adminBranchId, fn($q) => $q->where('branch_id', $adminBranchId));
        //     // No branch filter when adminBranchId is null = show all branches

        //     $activeOrders = (clone $base)
        //         ->whereNotIn('status', ['paid', 'checkout', 'cancelled'])
        //         ->latest()->get();

        //     $paymentOrders = (clone $base)
        //         ->where(function ($q) {
        //             $q->where(fn($q2) => $q2->where('is_parcel', false)->whereIn('status', ['served', 'checkout']))
        //               ->orWhere(fn($q2) => $q2->where('is_parcel', true)->where('status', 'ready'));
        //         })
        //         ->latest()->get();

        //     return response()->json([
        //         'orders'         => $activeOrders->map(fn($o) => $this->mapOrder($o))->values(),
        //         'payment_orders' => $paymentOrders->map(fn($o) => $this->mapOrder($o))->values(),
        //     ]);
        // }

        if ($panel === 'admin_waiter' || $panel === 'manager_waiter') {
            $scopedBranchId = ($panel === 'manager_waiter')
                ? $branchId  // manager is always scoped to their own branch
                : ($request->filled('branch_id') ? (int) $request->branch_id : null);

            $base = $this->baseQuery($tenantId)
                ->when($scopedBranchId, fn($q) => $q->where('branch_id', $scopedBranchId));

            $activeOrders = (clone $base)
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->latest()->get();

            $paymentOrders = (clone $base)
                ->where(function ($q) {
                    $q->where(fn($q2) => $q2->where('is_parcel', false)->whereIn('status', ['served', 'checkout']))
                    ->orWhere(fn($q2) => $q2->where('is_parcel', true)->where('status', 'ready'));
                })
                ->latest()->get();

            return response()->json([
                'orders'         => $activeOrders->map(fn($o) => $this->mapOrder($o))->values(),
                'payment_orders' => $paymentOrders->map(fn($o) => $this->mapOrder($o))->values(),
            ]);
        }


        // All other panels: scope to user's branch
        $query = $this->baseQuery($tenantId)
            ->when(
                $branchId,
                fn($q) => $q->where('branch_id', $branchId),
                fn($q) => $q->whereNull('branch_id')
            );

        match ($panel) {
            'cook' => $query->whereIn('status', ['pending', 'preparing', 'ready', 'served']),

            'cashier' => $query->where(function ($q) {
                $q->where(fn($q2) => $q2->where('is_parcel', false)->whereIn('status', ['served', 'checkout']))
                  ->orWhere(fn($q2) => $q2->where('is_parcel', true)->where('status', 'ready'));
            }),

            'cashier_instant' => $query->whereNotIn('status', ['paid', 'cancelled']),

            'cashier_parcels' => $query->where('is_parcel', true)
                                       ->whereNotIn('status', ['paid', 'cancelled']),

            default =>
                $query->whereNotIn('status', ['paid', 'checkout', 'cancelled']),
        };

        $orders = $query->latest()->get()->map(fn($o) => $this->mapOrder($o));

        return response()->json(['orders' => $orders->values()]);
    }

    private function baseQuery(int $tenantId)
    {
        return Order::with([
            'table.category',
            'branch.gstSlab',
            'user',
            'orderItems' => fn($q) => $q->withoutGlobalScopes()
                ->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()]),
        ])
        ->where('tenant_id', $tenantId)
        ->whereDate('created_at', today());
    }

    private function mapOrder(Order $order): array
    {
        $base     = (float) $order->total_amount;
        $slab     = $order->branch?->gstSlab;
        $mode     = $order->branch?->gst_mode;
        $grand    = $base;
        $cgstAmt  = 0;
        $sgstAmt  = 0;
        $gstEnabled = false;
        if ($slab && $mode) {
            $gstEnabled = true;
            $cgstPct = (float) $slab->cgst_rate;
            $sgstPct = (float) $slab->sgst_rate;
            if ($mode === 'excluded') {
                $cgstAmt = round($base * $cgstPct / 100, 2);
                $sgstAmt = round($base * $sgstPct / 100, 2);
                $grand   = $base + $cgstAmt + $sgstAmt;
            } else {
                $totalPct = $cgstPct + $sgstPct;
                $baseEx   = round($base * 100 / (100 + $totalPct), 2);
                $cgstAmt  = round($baseEx * $cgstPct / 100, 2);
                $sgstAmt  = round($baseEx * $sgstPct / 100, 2);
                $grand    = $base;
            }
        }

        return [
            'id'             => $order->id,
            'status'         => $order->status,
            'is_parcel'      => (bool) $order->is_parcel,
            'total_amount'   => $base,
            'grand_total'    => $grand,
            'gst_enabled'    => $gstEnabled,
            'gst_mode'       => $mode,
            'cgst_pct'       => $slab ? (float) $slab->cgst_rate : 0,
            'sgst_pct'       => $slab ? (float) $slab->sgst_rate : 0,
            'cgst_amount'    => $cgstAmt,
            'sgst_amount'    => $sgstAmt,
            'table_number'   => $order->table?->table_number,
            'table_category' => $order->table?->category?->name,
            'branch_name'    => $order->branch?->name,
            'created_at'     => $order->created_at->format('h:i A'),
            'created_at_ts'  => $order->created_at->timestamp,
            'customer_notes' => $order->customer_notes,
            'user_name'      => $order->user?->name,
            'payment_mode'   => $order->payment_mode,
            'upi_id'         => $order->branch?->upi_id,
            'items'          => $order->orderItems->map(fn($item) => [
                'id'       => $item->id,
                'status'   => $item->status,
                'name'     => $item->menuItem?->name ?? '[Deleted Item]',
                'quantity' => $item->quantity,
                'price'    => (float) $item->price,
                'notes'    => $item->notes,
            ])->values(),
        ];
    }
}

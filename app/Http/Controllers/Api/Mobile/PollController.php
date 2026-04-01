<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\{Order, Branch};
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function poll(Request $request)
    {
        $employee = $request->user();
        $tenantId = (int) $employee->tenant_id;
        $branchId = $employee->branch_id ?? null;
        $panel    = $request->query('panel', 'waiter');

        // Load branch.gstSlab for cashier panels so GST is computed correctly
        $isCashierPanel = in_array($panel, ['cashier', 'cashier_instant', 'cashier_parcels']);

        $with = ['table.category', 'orderItems' => fn($q) => $q->withoutGlobalScopes()
            ->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()])];

        if ($isCashierPanel) {
            $with[] = 'branch.gstSlab';
        }

        $query = Order::with($with)
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
                $q->where(fn($q2) => $q2->where('is_parcel', false)->whereIn('status', ['served', 'checkout']))
                  ->orWhere(fn($q2) => $q2->where('is_parcel', true)->where('status', 'ready'));
            }),

            'cashier_instant' => $query->whereNotIn('status', ['paid', 'cancelled']),

            'cashier_parcels' => $query->where('is_parcel', true)
                                       ->whereNotIn('status', ['paid', 'cancelled']),

            default => // waiter
                $query->whereNotIn('status', ['paid', 'checkout', 'cancelled']),
        };

        // Load branch once for UPI ID (cashier panels only)
        $branch = $isCashierPanel && $branchId
            ? Branch::with('gstSlab')->find($branchId)
            : null;

        $orders = $query->latest()->get()->map(function ($order) use ($isCashierPanel, $branch) {
            $gst     = ['enabled' => false];
            $grand   = (float) $order->total_amount;
            $upiId   = null;
            $upiUri  = null;

            if ($isCashierPanel) {
                $gst   = $this->computeGst($order, $branch);
                $grand = $gst['enabled'] ? $gst['grand'] : (float) $order->total_amount;

                $upiId = $branch?->upi_id;
                if ($upiId) {
                    $upiUri = 'upi://pay?pa=' . urlencode($upiId)
                            . '&am=' . number_format($grand, 2, '.', '')
                            . '&cu=INR';
                }
            }

            return [
                'id'             => $order->id,
                'status'         => $order->status,
                'is_parcel'      => (bool) $order->is_parcel,
                'subtotal'       => (float) $order->total_amount,
                'grand_total'    => $grand,
                'total_amount'   => (float) $order->total_amount, // keep for waiter/chef compat
                'customer_notes' => $order->customer_notes,
                'payment_mode'   => $order->payment_mode,
                'upi_id'         => $upiId,
                'upi_uri'        => $upiUri,
                'gst'            => $gst,
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
                    'subtotal' => (float) ($i->price * $i->quantity),
                    'status'   => $i->status,
                    'notes'    => $i->notes,
                ])->values(),
            ];
        });

        return response()->json(['orders' => $orders]);
    }

    private function computeGst(Order $order, ?Branch $branch): array
    {
        $slab = $branch?->gstSlab;
        $mode = $branch?->gst_mode;

        if (!$slab || !$mode) {
            return ['enabled' => false];
        }

        $base    = (float) $order->total_amount;
        $cgstPct = (float) $slab->cgst_rate;
        $sgstPct = (float) $slab->sgst_rate;

        if ($mode === 'excluded') {
            $cgst  = round($base * $cgstPct / 100, 2);
            $sgst  = round($base * $sgstPct / 100, 2);
            $grand = $base + $cgst + $sgst;
        } else {
            $totalPct = $cgstPct + $sgstPct;
            $base     = round($base * 100 / (100 + $totalPct), 2);
            $cgst     = round($base * $cgstPct / 100, 2);
            $sgst     = round($base * $sgstPct / 100, 2);
            $grand    = (float) $order->total_amount;
        }

        return [
            'enabled'   => true,
            'mode'      => $mode,
            'cgst_pct'  => $cgstPct,
            'sgst_pct'  => $sgstPct,
            'total_pct' => $cgstPct + $sgstPct,
            'base'      => $base,
            'cgst'      => $cgst,
            'sgst'      => $sgst,
            'grand'     => $grand,
        ];
    }
}

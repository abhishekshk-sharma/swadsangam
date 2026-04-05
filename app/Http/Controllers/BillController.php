<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function show(Request $request, $orderId)
    {
        $query = Order::withoutGlobalScope('tenant')
            ->with([
                'table',
                'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()]),
                'tenant.gstSlab',
                'branch.gstSlab',
            ])
            ->where('status', 'paid');

        if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'bill_hidden')) {
            $query->where('bill_hidden', false);
        }

        $order = $query->findOrFail($orderId);
        $gst   = $this->computeGst($order);

        return view('bill.show', compact('order', 'gst'));
    }

    private function computeGst(Order $order): array
    {
        // Branch-level GST takes priority over tenant-level
        $branch = $order->branch;
        $tenant = $order->tenant;

        $slab = $branch?->gstSlab ?? $tenant?->gstSlab;
        $mode = ($branch?->gst_slab_id ? $branch->gst_mode : null)
             ?? ($tenant?->gst_slab_id ? $tenant->gst_mode : null);

        if (!$slab || !$mode) {
            return ['enabled' => false];
        }

        $base    = (float) $order->total_amount;
        $cgstPct = (float) $slab->cgst_rate;
        $sgstPct = (float) $slab->sgst_rate;

        if ($mode === 'excluded') {
            // GST added on top — base is item total, grand total is higher
            $cgst  = round($base * $cgstPct / 100, 2);
            $sgst  = round($base * $sgstPct / 100, 2);
            $grand = $base + $cgst + $sgst;
        } else {
            // GST already included — back-calculate base
            $totalPct = $cgstPct + $sgstPct;
            $base     = round($base * 100 / (100 + $totalPct), 2);
            $cgst     = round($base * $cgstPct / 100, 2);
            $sgst     = round($base * $sgstPct / 100, 2);
            $grand    = (float) $order->total_amount;
        }

        return [
            'enabled'   => true,
            'mode'      => $mode,
            'slab_name' => $slab->name,
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

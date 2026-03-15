<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class BillController extends Controller
{
    public function show(Request $request, $orderId)
    {
        if (!URL::hasValidSignature($request)) {
            abort(403, 'Invalid or expired bill link.');
        }

        $order = Order::withoutGlobalScope('tenant')
            ->with(['table', 'orderItems.menuItem', 'tenant'])
            ->where('status', 'paid')
            ->findOrFail($orderId);

        return view('bill.show', compact('order'));
    }
}

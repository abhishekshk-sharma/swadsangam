<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function show(Request $request, $orderId)
    {
        $order = Order::withoutGlobalScope('tenant')
            ->with(['table', 'orderItems.menuItem', 'tenant'])
            ->where('status', 'paid')
            ->findOrFail($orderId);

        return view('bill.show', compact('order'));
    }
}

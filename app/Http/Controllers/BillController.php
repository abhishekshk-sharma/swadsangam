<?php

namespace App\Http\Controllers;

use App\Models\Order;

class BillController extends Controller
{
    public function show($orderId)
    {
        // Public route — bypass global scope, find by ID only
        $order = Order::withoutGlobalScope('tenant')
            ->with(['table', 'orderItems.menuItem', 'tenant'])
            ->where('status', 'paid')
            ->findOrFail($orderId);

        return view('bill.show', compact('order'));
    }
}

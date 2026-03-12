<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;

class OrderStatusUpdated
{
    use Dispatchable;

    public $order;
    public $oldStatus;

    public function __construct(Order $order, $oldStatus)
    {
        $this->order = $order->load('table', 'orderItems.menuItem');
        $this->oldStatus = $oldStatus;
    }
}

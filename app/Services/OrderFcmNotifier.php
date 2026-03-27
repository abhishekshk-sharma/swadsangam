<?php

namespace App\Services;

use App\Models\Order;

class OrderFcmNotifier
{
    // FCM disabled — app uses polling only.
    public function notifyOrderCreated(Order $order): void {}
    public function notifyStatusChanged(Order $order, string $oldStatus): void {}
}

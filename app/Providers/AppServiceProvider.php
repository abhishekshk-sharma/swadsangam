<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\OrderStatusUpdated;
use App\Services\OrderFcmNotifier;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Event::listen(OrderCreated::class, function (OrderCreated $event) {
            (new OrderFcmNotifier())->notifyOrderCreated($event->order);
        });

        Event::listen(OrderStatusUpdated::class, function (OrderStatusUpdated $event) {
            (new OrderFcmNotifier())->notifyStatusChanged($event->order, $event->oldStatus);
        });
    }
}

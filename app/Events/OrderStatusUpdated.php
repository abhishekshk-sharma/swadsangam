<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $oldStatus;

    public function __construct(Order $order, $oldStatus)
    {
        $this->order     = $order->load('table', 'orderItems.menuItem');
        $this->oldStatus = $oldStatus;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('orders.' . $this->order->tenant_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'order' => [
                'id'             => $this->order->id,
                'status'         => $this->order->status,
                'old_status'     => $this->oldStatus,
                'total_amount'   => (float) $this->order->total_amount,
                'table_number'   => $this->order->table->table_number ?? null,
                'customer_notes' => $this->order->customer_notes,
                'created_by_id'  => $this->order->user_id ?? null,
                'payment_mode'   => $this->order->payment_mode,
                'created_at'     => $this->order->created_at->format('h:i A'),
                'items'          => $this->order->orderItems->map(fn($i) => [
                    'id'       => $i->id,
                    'name'     => $i->menuItem->name,
                    'quantity' => $i->quantity,
                    'price'    => (float) $i->price,
                    'status'   => $i->status,
                    'notes'    => $i->notes,
                ])->values()->toArray(),
            ],
        ];
    }
}

<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order->fresh(['table', 'orderItems.menuItem']) ?? $order->load('table', 'orderItems.menuItem');
    }

    public function broadcastOn(): array
    {
        return [new Channel('orders.' . $this->order->tenant_id)];
    }

    public function broadcastAs(): string
    {
        return 'OrderCreated';
    }

    public function broadcastWith(): array
    {
        $items = $this->order->orderItems->map(function ($i) {
            return [
                'id'       => $i->id,
                'name'     => $i->menuItem ? $i->menuItem->name : '[Deleted]',
                'quantity' => $i->quantity,
                'price'    => (float) $i->price,
                'status'   => $i->status,
                'notes'    => $i->notes,
            ];
        })->values()->toArray();

        return [
            'order' => [
                'id'             => $this->order->id,
                'daily_number'   => $this->order->daily_number ?? $this->order->id,
                'status'         => $this->order->status,
                'is_parcel'      => $this->order->is_parcel,
                'total_amount'   => (float) $this->order->total_amount,
                'customer_notes' => $this->order->customer_notes,
                'table_number'   => $this->order->table ? $this->order->table->table_number : null,
                'created_at'     => $this->order->created_at ? $this->order->created_at->format('h:i A') : now()->format('h:i A'),
                'items'          => $items,
            ],
        ];
    }
}

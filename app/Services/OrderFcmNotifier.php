<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Order;

class OrderFcmNotifier
{
    private FcmService $fcm;

    public function __construct()
    {
        $this->fcm = new FcmService();
    }

    /**
     * New order created → notify all chefs in the same branch/tenant.
     */
    public function notifyOrderCreated(Order $order): void
    {
        $label   = $order->is_parcel ? 'Parcel' : ('Table ' . ($order->table?->table_number ?? '?'));
        $count   = $order->orderItems()->count();
        $title   = 'New Order #' . ($order->daily_number ?? $order->id);
        $body    = "$label — $count item" . ($count !== 1 ? 's' : '');

        $this->fcm->sendMulti(
            $this->tokensForRole($order, 'chef'),
            $title,
            $body,
            ['order_id' => (string) $order->id, 'panel' => 'chef']
        );
    }

    /**
     * Order status changed → notify relevant roles.
     *
     * ready     → waiter who owns the order
     * paid      → waiter who owns the order
     * cancelled → chef tokens (so they stop preparing)
     */
    public function notifyStatusChanged(Order $order, string $oldStatus): void
    {
        $newStatus = $order->status;
        $label     = $order->is_parcel ? 'Parcel' : ('Table ' . ($order->table?->table_number ?? '?'));
        $num       = '#' . ($order->daily_number ?? $order->id);

        if ($newStatus === 'ready') {
            $token = Employee::withoutGlobalScopes()
                ->where('id', $order->user_id)
                ->value('fcm_token');

            if ($token) {
                $this->fcm->send($token, "Order $num Ready", "$label is ready to serve.",
                    ['order_id' => (string) $order->id, 'panel' => 'waiter']);
            }
        }

        if ($newStatus === 'paid') {
            $token = Employee::withoutGlobalScopes()
                ->where('id', $order->user_id)
                ->value('fcm_token');

            if ($token) {
                $this->fcm->send($token, "Order $num Paid", "$label payment received.",
                    ['order_id' => (string) $order->id, 'panel' => 'waiter']);
            }
        }

        if ($newStatus === 'cancelled') {
            $this->fcm->sendMulti(
                $this->tokensForRole($order, 'chef'),
                "Order $num Cancelled",
                "$label has been cancelled.",
                ['order_id' => (string) $order->id, 'panel' => 'chef']
            );
        }
    }

    private function tokensForRole(Order $order, string $role): array
    {
        return Employee::withoutGlobalScopes()
            ->where('tenant_id', $order->tenant_id)
            ->where('role', $role)
            ->where('is_active', true)
            ->when($order->branch_id,
                fn($q) => $q->where('branch_id', $order->branch_id),
                fn($q) => $q->whereNull('branch_id')
            )
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();
    }
}

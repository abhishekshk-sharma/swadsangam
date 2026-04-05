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
     * New order created → notify chefs + all cashiers (parcel goes straight to cashier).
     */
    public function notifyOrderCreated(Order $order): void
    {
        $label = $order->is_parcel ? 'Parcel' : ('Table ' . ($order->table?->table_number ?? '?'));
        $count = $order->orderItems()->count();
        $num   = '#' . ($order->daily_number ?? $order->id);
        $title = "New Order $num";
        $body  = "$label — $count item" . ($count !== 1 ? 's' : '');

        // Notify chefs
        $this->fcm->sendMulti(
            $this->tokensForRole($order, 'chef'),
            $title,
            $body,
            ['order_id' => (string) $order->id, 'panel' => 'chef']
        );

        // Notify cashiers (they need to see all new orders)
        $this->fcm->sendMulti(
            $this->tokensForRole($order, 'cashier'),
            $title,
            $body,
            ['order_id' => (string) $order->id, 'panel' => 'cashier']
        );
    }

    /**
     * Items added to existing order → notify chefs.
     */
    public function notifyItemsAdded(Order $order, int $newItemCount): void
    {
        $label = $order->is_parcel ? 'Parcel' : ('Table ' . ($order->table?->table_number ?? '?'));
        $num   = '#' . ($order->daily_number ?? $order->id);

        $this->fcm->sendMulti(
            $this->tokensForRole($order, 'chef'),
            "Items Added to Order $num",
            "$label — $newItemCount new item" . ($newItemCount !== 1 ? 's' : '') . ' added',
            ['order_id' => (string) $order->id, 'panel' => 'chef']
        );
    }

    /**
     * Order status changed → notify relevant panels.
     *
     * pending/preparing → (already handled by notifyOrderCreated / notifyItemsAdded)
     * ready     → waiter who owns the order
     * checkout  → cashier panel
     * paid      → waiter who owns the order
     * cancelled → chef + cashier
     */
    public function notifyStatusChanged(Order $order, string $oldStatus): void
    {
        $newStatus = $order->status;
        $label     = $order->is_parcel ? 'Parcel' : ('Table ' . ($order->table?->table_number ?? '?'));
        $num       = '#' . ($order->daily_number ?? $order->id);

        switch ($newStatus) {

            case 'preparing':
                // Chef already knows — no extra push needed
                break;

            case 'ready':
                // Notify the waiter who owns the order
                $token = $this->tokenForEmployee($order->user_id);
                if ($token) {
                    $this->fcm->send(
                        $token,
                        "Order $num Ready ✅",
                        "$label is ready to serve.",
                        ['order_id' => (string) $order->id, 'panel' => 'waiter']
                    );
                }
                // Notify cashiers for parcel orders (parcel ready = ready for payment)
                if ($order->is_parcel) {
                    $this->fcm->sendMulti(
                        $this->tokensForRole($order, 'cashier'),
                        "Parcel $num Ready for Payment",
                        "$label is ready — collect payment.",
                        ['order_id' => (string) $order->id, 'panel' => 'cashier']
                    );
                }
                break;

            case 'served':
                // No push needed — waiter did this action themselves
                break;

            case 'checkout':
                // Waiter sent to cashier — notify all cashiers
                $this->fcm->sendMulti(
                    $this->tokensForRole($order, 'cashier'),
                    "Order $num — Collect Payment 💰",
                    "$label is ready for payment.",
                    ['order_id' => (string) $order->id, 'panel' => 'cashier']
                );
                break;

            case 'paid':
                // Notify the waiter who owns the order
                $token = $this->tokenForEmployee($order->user_id);
                if ($token) {
                    $this->fcm->send(
                        $token,
                        "Order $num Paid ✅",
                        "$label — payment received.",
                        ['order_id' => (string) $order->id, 'panel' => 'waiter']
                    );
                }
                break;

            case 'cancelled':
                // Notify chefs so they stop preparing
                $this->fcm->sendMulti(
                    $this->tokensForRole($order, 'chef'),
                    "Order $num Cancelled ❌",
                    "$label has been cancelled.",
                    ['order_id' => (string) $order->id, 'panel' => 'chef']
                );
                // Notify cashiers
                $this->fcm->sendMulti(
                    $this->tokensForRole($order, 'cashier'),
                    "Order $num Cancelled ❌",
                    "$label has been cancelled.",
                    ['order_id' => (string) $order->id, 'panel' => 'cashier']
                );
                break;
        }
    }

    private function tokenForEmployee(?int $userId): ?string
    {
        if (!$userId) return null;
        return Employee::withoutGlobalScopes()
            ->where('id', $userId)
            ->whereNotNull('fcm_token')
            ->value('fcm_token');
    }

    private function tokensForRole(Order $order, string $role): array
    {
        return Employee::withoutGlobalScopes()
            ->where('tenant_id', $order->tenant_id)
            ->where('role', $role)
            ->where('is_active', true)
            ->when(
                $order->branch_id,
                fn($q) => $q->where('branch_id', $order->branch_id),
                fn($q) => $q->whereNull('branch_id')
            )
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();
    }
}

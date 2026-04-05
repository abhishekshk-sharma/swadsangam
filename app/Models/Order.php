<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\BelongsToBranch;

class Order extends Model
{
    use BelongsToTenant, BelongsToBranch;
    protected $fillable = ['tenant_id', 'branch_id', 'table_id', 'user_id', 'assigned_to', 'cashier_id', 'status', 'preparation_time', 'ready_at', 'total_amount', 'grand_total', 'bill_hidden', 'daily_number', 'payment_mode', 'paid_at', 'customer_notes', 'is_parcel'];
    protected $casts = ['ready_at' => 'datetime', 'paid_at' => 'datetime', 'bill_hidden' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            try {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('orders', 'daily_number')) {
                    return;
                }
                $query = static::withoutGlobalScopes()
                    ->where('tenant_id', (int) $order->tenant_id)
                    ->whereDate('created_at', now()->toDateString());

                if ($order->branch_id) {
                    $query->where('branch_id', (int) $order->branch_id);
                } else {
                    $query->whereNull('branch_id');
                }

                $order->daily_number = ((int) $query->max('daily_number')) + 1;
            } catch (\Throwable $e) {
                // Column missing on production — skip silently
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function user()
    {
        return $this->belongsTo(Employee::class, 'user_id');
    }

    public function table()
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}

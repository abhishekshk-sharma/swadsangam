<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\BelongsToBranch;

class Order extends Model
{
    use BelongsToTenant, BelongsToBranch;
    protected $fillable = ['tenant_id', 'branch_id', 'table_id', 'user_id', 'assigned_to', 'cashier_id', 'status', 'preparation_time', 'ready_at', 'total_amount', 'grand_total', 'bill_hidden', 'payment_mode', 'paid_at', 'customer_notes', 'is_parcel'];
    protected $casts = ['ready_at' => 'datetime', 'paid_at' => 'datetime', 'bill_hidden' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $last = static::withoutGlobalScopes()
                ->where('tenant_id', $order->tenant_id)
                ->where('branch_id', $order->branch_id)
                ->whereDate('created_at', now()->toDateString())
                ->max('daily_number');

            $order->daily_number = ($last ?? 0) + 1;
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

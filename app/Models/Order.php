<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\BelongsToBranch;

class Order extends Model
{
    use BelongsToTenant, BelongsToBranch;
    protected $fillable = ['tenant_id', 'branch_id', 'table_id', 'user_id', 'cashier_id', 'status', 'preparation_time', 'ready_at', 'total_amount', 'payment_mode', 'paid_at', 'customer_notes', 'is_parcel'];
    protected $casts = ['ready_at' => 'datetime', 'paid_at' => 'datetime'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
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
}

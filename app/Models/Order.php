<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Order extends Model
{
    use BelongsToTenant;
    protected $fillable = ['table_id', 'user_id', 'status', 'preparation_time', 'ready_at', 'total_amount', 'payment_mode', 'paid_at'];
    protected $casts = ['ready_at' => 'datetime', 'paid_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
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

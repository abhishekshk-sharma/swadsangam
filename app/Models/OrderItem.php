<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class OrderItem extends Model
{
    use BelongsToTenant;
    protected $fillable = ['order_id', 'menu_item_id', 'quantity', 'price', 'status'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}

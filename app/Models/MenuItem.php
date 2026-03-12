<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class MenuItem extends Model
{
    use BelongsToTenant;
    protected $fillable = ['name', 'description', 'price', 'image', 'category', 'is_available', 'menu_category_id'];

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function menuCategory()
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}

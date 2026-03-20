<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\BelongsToBranch;

class MenuItem extends Model
{
    use BelongsToTenant, BelongsToBranch;
    protected $fillable = ['tenant_id', 'branch_id', 'name', 'description', 'price', 'image', 'is_available', 'menu_category_id'];

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

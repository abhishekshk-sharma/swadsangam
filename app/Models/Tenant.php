<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = ['name', 'slug', 'domain', 'status'];

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function tables()
    {
        return $this->hasMany(RestaurantTable::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}

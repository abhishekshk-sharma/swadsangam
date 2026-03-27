<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = ['name', 'slug', 'domain', 'status', 'gst_slab_id', 'gst_mode'];

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

    public function gstSlab()
    {
        return $this->belongsTo(GstSlab::class);
    }
}

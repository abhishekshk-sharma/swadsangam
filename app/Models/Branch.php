<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['tenant_id', 'name', 'address', 'phone', 'upi_id', 'is_active', 'gst_slab_id', 'gst_mode', 'gst_number'];

    protected $casts = ['is_active' => 'boolean'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function tables()
    {
        return $this->hasMany(RestaurantTable::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function gstSlab()
    {
        return $this->belongsTo(\App\Models\GstSlab::class);
    }
}

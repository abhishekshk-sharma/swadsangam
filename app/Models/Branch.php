<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['tenant_id', 'name', 'address', 'phone', 'is_active'];

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
}

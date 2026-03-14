<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TableCategory extends Model
{
    protected $fillable = ['tenant_id', 'name', 'description'];

    protected static function booted()
    {
        // Auto-set tenant_id on create
        static::creating(function ($model) {
            if (!$model->tenant_id && $tenantId = session('tenant_id')) {
                $model->tenant_id = $tenantId;
            }
        });
    }

    public function tables()
    {
        return $this->hasMany(RestaurantTable::class, 'category_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // Scope to get categories accessible by current tenant (global + own)
    public function scopeAccessibleByTenant(Builder $query)
    {
        return $query->where(function($q) {
            $q->whereNull('tenant_id')
              ->orWhere('tenant_id', session('tenant_id'));
        });
    }
}

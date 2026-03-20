<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TableCategory extends Model
{
    protected $fillable = ['tenant_id', 'branch_id', 'name', 'description'];

    protected static function booted()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $user = Auth::guard('admin')->user() ?? Auth::guard('employee')->user();
            if ($user && $user->tenant_id) {
                $builder->where(function ($q) use ($user) {
                    $q->whereNull('table_categories.tenant_id')
                      ->orWhere('table_categories.tenant_id', $user->tenant_id);
                });
            }
        });

        static::creating(function ($model) {
            $user = Auth::guard('admin')->user() ?? Auth::guard('employee')->user();
            if (!$model->tenant_id && $user) {
                $model->tenant_id = $user->tenant_id;
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
}

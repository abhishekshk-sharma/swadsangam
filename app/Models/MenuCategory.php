<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MenuCategory extends Model
{
    protected $fillable = ['tenant_id', 'branch_id', 'name', 'description', 'sort_order'];

    protected static function booted()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $user = Auth::guard('admin')->user() ?? Auth::guard('employee')->user();
            if ($user && $user->tenant_id) {
                $builder->where(function ($q) use ($user) {
                    $q->whereNull('menu_categories.tenant_id')
                      ->orWhere('menu_categories.tenant_id', $user->tenant_id);
                });
            }
        });

        static::addGlobalScope('sorted', function (Builder $builder) {
            $builder->orderBy('menu_categories.sort_order');
        });

        static::creating(function ($model) {
            $user = Auth::guard('admin')->user() ?? Auth::guard('employee')->user();
            if (!$model->tenant_id && $user) {
                $model->tenant_id = $user->tenant_id;
            }
        });
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'menu_category_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}

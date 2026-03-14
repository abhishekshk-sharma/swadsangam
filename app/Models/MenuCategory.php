<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MenuCategory extends Model
{
    protected $fillable = ['tenant_id', 'name', 'description'];

    protected static function booted()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if ($tenantId = self::resolveTenantId()) {
                $builder->where(function ($q) use ($tenantId) {
                    $q->whereNull('menu_categories.tenant_id')
                      ->orWhere('menu_categories.tenant_id', $tenantId);
                });
            }
        });

        static::creating(function ($model) {
            if (!$model->tenant_id) {
                $model->tenant_id = self::resolveTenantId();
            }
        });
    }

    protected static function resolveTenantId(): ?int
    {
        $user = Auth::guard('admin')->user() ?? Auth::guard('employee')->user();
        if ($user && $user->tenant_id) return (int) $user->tenant_id;
        return session('tenant_id') ? (int) session('tenant_id') : null;
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

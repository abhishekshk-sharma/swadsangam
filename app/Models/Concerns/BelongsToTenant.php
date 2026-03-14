<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if ($tenantId = self::resolveTenantId()) {
                $builder->where(static::qualifyTenantColumn(), $tenantId);
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
        // Read from app container — set by IdentifyTenant middleware and login
        // Never call Auth::guard()->user() here — causes infinite recursion
        // because loading the user model triggers this scope again
        if (app()->bound('current_tenant_id')) {
            return (int) app('current_tenant_id');
        }
        return session('tenant_id') ? (int) session('tenant_id') : null;
    }

    protected static function qualifyTenantColumn(): string
    {
        return (new static)->getTable() . '.tenant_id';
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}

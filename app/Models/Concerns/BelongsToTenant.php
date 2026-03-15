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
        if (app()->bound('current_tenant_id')) {
            $id = (int) app('current_tenant_id');
            return $id > 0 ? $id : null;
        }
        return null; // never fall back to session alone — too risky
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

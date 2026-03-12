<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        // Auto-scope all queries to current tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            if ($tenantId = session('tenant_id')) {
                $builder->where('tenant_id', $tenantId);
            }
        });

        // Auto-set tenant_id on create
        static::creating(function ($model) {
            if (!$model->tenant_id && $tenantId = session('tenant_id')) {
                $model->tenant_id = $tenantId;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}

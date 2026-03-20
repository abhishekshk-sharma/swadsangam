<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToBranch
{
    protected static function bootBelongsToBranch()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if ($branchId = self::resolveCurrentBranchId()) {
                $builder->where(static::qualifyBranchColumn(), $branchId);
            }
        });

        static::creating(function ($model) {
            if (!$model->branch_id && $branchId = self::resolveCurrentBranchId()) {
                $model->branch_id = $branchId;
            }
        });
    }

    protected static function resolveCurrentBranchId(): ?int
    {
        if (app()->bound('current_branch_id')) {
            $id = (int) app('current_branch_id');
            return $id > 0 ? $id : null;
        }
        return null;
    }

    protected static function qualifyBranchColumn(): string
    {
        return (new static)->getTable() . '.branch_id';
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}

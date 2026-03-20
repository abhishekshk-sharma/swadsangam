<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BaseManagerController extends Controller
{
    protected function manager()
    {
        return Auth::guard('employee')->user();
    }

    protected function tenantId(): int
    {
        return (int) $this->manager()->tenant_id;
    }

    protected function branchId(): ?int
    {
        return $this->manager()->branch_id;
    }

    protected function scopeBranch($query, string $column = 'branch_id')
    {
        $branchId = $this->branchId();
        return $branchId
            ? $query->where($column, $branchId)
            : $query->whereNull($column);
    }

    protected function findForTenant(string $model, int $id)
    {
        return $model::where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->firstOrFail();
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class BaseAdminController extends Controller
{
    protected function tenantId(): int
    {
        $user = auth()->guard('admin')->user() ?? auth()->guard('employee')->user();
        if (!$user) abort(403);
        return (int) $user->tenant_id;
    }

    protected function findForTenant(string $model, int $id)
    {
        return $model::where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->firstOrFail();
    }
}

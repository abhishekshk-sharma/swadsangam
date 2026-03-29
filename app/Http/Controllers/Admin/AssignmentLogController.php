<?php

namespace App\Http\Controllers\Admin;

use App\Models\{OrderAssignmentLog, Branch};
use Illuminate\Http\Request;

class AssignmentLogController extends BaseAdminController
{
    public function index(Request $request)
    {
        $logs = OrderAssignmentLog::with(['order.table', 'order.branch', 'fromUser', 'toUser'])
            ->where('tenant_id', $this->tenantId())
            ->when($request->filled('branch_id'), fn($q) =>
                $q->whereHas('order', fn($q2) => $q2->where('branch_id', $request->branch_id))
            )
            ->when($request->filled('date'), fn($q) => $q->whereDate('created_at', $request->date))
            ->latest()
            ->paginate(30);

        $branches       = Branch::where('tenant_id', $this->tenantId())->where('is_active', true)->get();
        $selectedBranch = $request->branch_id;

        return view('admin.assignment-logs.index', compact('logs', 'branches', 'selectedBranch'));
    }
}

<?php

namespace App\Http\Controllers\Manager;

use App\Models\OrderAssignmentLog;
use Illuminate\Http\Request;

class AssignmentLogController extends BaseManagerController
{
    public function index(Request $request)
    {
        $logs = OrderAssignmentLog::with(['order.table', 'fromUser', 'toUser'])
            ->where('tenant_id', $this->tenantId())
            ->when($this->branchId(), fn($q) => $q->whereHas('order', fn($q2) => $q2->where('branch_id', $this->branchId())))
            ->when($request->filled('date'), fn($q) => $q->whereDate('created_at', $request->date))
            ->latest()
            ->paginate(30);

        return view('manager.assignment-logs.index', compact('logs'));
    }
}

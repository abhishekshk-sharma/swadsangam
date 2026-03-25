<?php

namespace App\Http\Controllers\Admin;

use App\Models\OrderAssignmentLog;
use Illuminate\Http\Request;

class AssignmentLogController extends BaseAdminController
{
    public function index(Request $request)
    {
        $logs = OrderAssignmentLog::with(['order.table', 'fromUser', 'toUser'])
            ->where('tenant_id', $this->tenantId())
            ->when($request->filled('date'), fn($q) => $q->whereDate('created_at', $request->date))
            ->latest()
            ->paginate(30);

        return view('admin.assignment-logs.index', compact('logs'));
    }
}

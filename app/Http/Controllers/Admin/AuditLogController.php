<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display the admin audit logs.
     */
    public function index(Request $request): View
    {
        $query = AdminAuditLog::with('admin');

        // Filter by action type
        if ($request->has('action_type') && $request->action_type) {
            $query->where('action_type', $request->action_type);
        }

        // Filter by admin
        if ($request->has('admin_id') && $request->admin_id) {
            $query->where('admin_id', $request->admin_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $auditLogs = $query->latest()->paginate(50);
        $actionTypes = AdminAuditLog::distinct('action_type')->pluck('action_type');
        $admins = User::where('role', 'admin')->get();

        return view('admin.audit-logs.index', [
            'auditLogs' => $auditLogs,
            'actionTypes' => $actionTypes,
            'admins' => $admins,
            'filters' => [
                'action_type' => $request->action_type,
                'admin_id' => $request->admin_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ],
        ]);
    }

    /**
     * Display the specified audit log entry.
     */
    public function show(AdminAuditLog $auditLog): View
    {
        return view('admin.audit-logs.show', ['auditLog' => $auditLog]);
    }
}

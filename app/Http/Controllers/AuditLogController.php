<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the audit logs.
     */
    public function index(Request $request)
    {
        $query = AuditLog::query();

        // Optional filtering
        if ($request->filled('table_name')) {
            $query->where('table_name', $request->input('table_name'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('changed_by')) {
            $query->where('changed_by', $request->input('changed_by'));
        }

        $logs = $query->latest('changed_at')->paginate(10);

        return view('auditlogs.index', compact('logs'));
    }

    /**
     * Display the specified audit log with decoded data.
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->data_old = json_decode($auditLog->data_old, true);
        $auditLog->data_new = json_decode($auditLog->data_new, true);

        return view('auditlogs.show', compact('auditLog'));
    }

    /**
     * Prevent creation of audit logs via UI.
     */
    public function create()
    {
        abort(405, 'Audit logs cannot be created manually.');
    }

    public function store(Request $request)
    {
        abort(405, 'Audit logs cannot be created manually.');
    }

    /**
     * Prevent editing audit logs via UI.
     */
    public function edit(AuditLog $auditLog)
    {
        abort(405, 'Audit logs cannot be edited.');
    }

    public function update(Request $request, AuditLog $auditLog)
    {
        abort(405, 'Audit logs cannot be updated.');
    }

    /**
     * Prevent deletion of audit logs.
     */
    public function destroy(AuditLog $auditLog)
    {
        abort(405, 'Audit logs cannot be deleted.');
    }
}

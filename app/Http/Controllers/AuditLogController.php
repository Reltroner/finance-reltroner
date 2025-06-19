<?php
// app/Http/Controllers/AuditLogController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs, with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::query();

        // Optional filters for easier audit trail browsing
        if ($request->filled('table_name')) {
            $query->where('table_name', $request->input('table_name'));
        }
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }
        if ($request->filled('changed_by')) {
            $query->where('changed_by', $request->input('changed_by'));
        }

        $logs = $query->orderByDesc('changed_at')->paginate(25);

        return response()->json($logs);
    }

    /**
     * Display the specified audit log (with decoded JSON for easy frontend use).
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        // Decode old/new data for frontend readability
        $auditLog->data_old = json_decode($auditLog->data_old, true);
        $auditLog->data_new = json_decode($auditLog->data_new, true);
        return response()->json($auditLog);
    }

    /**
     * Block creation of audit logs via API.
     */
    public function store()
    {
        return response()->json([
            'message' => 'Audit logs cannot be created via API.'
        ], 405);
    }

    /**
     * Block updating audit logs via API.
     */
    public function update()
    {
        return response()->json([
            'message' => 'Audit logs cannot be updated via API.'
        ], 405);
    }

    /**
     * Block deleting audit logs via API.
     */
    public function destroy()
    {
        return response()->json([
            'message' => 'Audit logs cannot be deleted via API.'
        ], 405);
    }
}

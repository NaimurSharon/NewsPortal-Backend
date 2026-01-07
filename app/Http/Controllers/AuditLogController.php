<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs
     */
    public function index(Request $request)
    {
        // Allow filtering by user, action, or date
        return AuditLog::with('user:id,name')
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->latest()
            ->paginate(50);
    }

    /**
     * Show
     */
    public function show(AuditLog $auditLog)
    {
        return $auditLog->load('user');
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a paginated list of audit logs with filtering
     * 
     * GET /api/v1/admin/audit-logs
     */
    public function index(Request $request): JsonResponse
    {
        // Following Laravel best practices: thin controller, business logic in queries
        $logs = AuditLog::query()
            ->with(['user:id,name,email']) // Eager loading to avoid N+1
            ->when($request->event, function ($query, $event) {
                $query->where('event', $event);
            })
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('user_email', 'like', "%{$search}%")
                      ->orWhere('event', 'like', "%{$search}%")
                      ->orWhere('ip_address', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 50);

        return response()->json([
            'success' => true,
            'data' => $logs,
            'filters' => [
                'event' => $request->event,
                'user_id' => $request->user_id,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'search' => $request->search,
            ]
        ]);
    }

    /**
     * Get unique event types for filtering dropdown
     * 
     * GET /api/v1/admin/audit-logs/events
     */
    public function events(): JsonResponse
    {
        $events = AuditLog::distinct()
            ->pluck('event')
            ->sort()
            ->values();

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    /**
     * Show specific audit log details
     * 
     * GET /api/v1/admin/audit-logs/{id}
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        // Eager load relationships
        $auditLog->load(['user:id,name,email', 'auditable']);

        return response()->json([
            'success' => true,
            'data' => $auditLog
        ]);
    }
}

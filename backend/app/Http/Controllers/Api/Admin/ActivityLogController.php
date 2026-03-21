<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $query = ActivityLog::with(['causer:id,first_name,last_name,email'])
            ->orderBy('created_at', 'desc');

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($module = $request->input('module')) {
            $query->where('module', $module);
        }

        if ($causerId = $request->input('causer_id')) {
            $query->where('causer_id', $causerId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('causer_name', 'like', "%{$search}%")
                    ->orWhere('subject_name', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->input('per_page', 50), 100);
        $logs = $query->paginate($perPage);

        $logs->getCollection()->transform(function ($log) {
            return [
                'id' => $log->id,
                'timestamp' => $log->created_at->toISOString(),
                'admin_id' => $log->causer_id,
                'admin_name' => $log->causer_name ?? ($log->causer ? $log->causer->full_name : 'System'),
                'admin_email' => $log->causer?->email ?? null,
                'action' => ucfirst($log->action),
                'module' => ucfirst($log->module ?? ''),
                'description' => $log->description,
                'subject_type' => $log->subject_type,
                'subject_id' => $log->subject_id,
                'subject_name' => $log->subject_name,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'url' => $log->url,
                'method' => $log->method,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    public function show(ActivityLog $activityLog): JsonResponse
    {
        $activityLog->load(['causer:id,first_name,last_name,email']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $activityLog->id,
                'timestamp' => $activityLog->created_at->toISOString(),
                'admin_id' => $activityLog->causer_id,
                'admin_name' => $activityLog->causer_name ?? ($activityLog->causer ? $activityLog->causer->full_name : 'System'),
                'admin_email' => $activityLog->causer?->email ?? null,
                'action' => ucfirst($activityLog->action),
                'module' => ucfirst($activityLog->module ?? ''),
                'description' => $activityLog->description,
                'subject_type' => $activityLog->subject_type,
                'subject_id' => $activityLog->subject_id,
                'subject_name' => $activityLog->subject_name,
                'old_values' => $activityLog->old_values,
                'new_values' => $activityLog->new_values,
                'properties' => $activityLog->properties,
                'ip_address' => $activityLog->ip_address,
                'user_agent' => $activityLog->user_agent,
                'url' => $activityLog->url,
                'method' => $activityLog->method,
            ],
        ]);
    }

    public function statistics(): JsonResponse
    {
        $stats = [
            'total_logs' => ActivityLog::count(),
            'today_logs' => ActivityLog::whereDate('created_at', today())->count(),
            'this_week_logs' => ActivityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month_logs' => ActivityLog::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'actions_count' => ActivityLog::selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->pluck('count', 'action'),
            'modules_count' => ActivityLog::selectRaw('module, COUNT(*) as count')
                ->whereNotNull('module')
                ->groupBy('module')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->pluck('count', 'module'),
            'top_admins' => ActivityLog::selectRaw('causer_id, causer_name, COUNT(*) as count')
                ->whereNotNull('causer_id')
                ->groupBy('causer_id', 'causer_name')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->map(fn($log) => [
                    'admin_id' => $log->causer_id,
                    'admin_name' => $log->causer_name,
                    'count' => $log->count,
                ]),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}

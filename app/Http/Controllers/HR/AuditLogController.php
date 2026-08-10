<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Super Admin');
    }

    /**
     * Show audit logs dashboard
     */
    public function index(Request $request)
    {
        $selectedAction = $request->get('action', '');
        $selectedModel = $request->get('model', '');
        $selectedUser = $request->integer('user_id', 0);
        $dateFrom = $request->get('from_date', '');
        $dateTo = $request->get('to_date', '');

        $query = AuditLog::with('user')
            ->orderByDesc('created_at');

        // Filter by action
        if ($selectedAction) {
            $query->where('action', $selectedAction);
        }

        // Filter by model type
        if ($selectedModel) {
            $query->where('model_type', 'like', '%' . $selectedModel . '%');
        }

        // Filter by user
        if ($selectedUser) {
            $query->where('user_id', $selectedUser);
        }

        // Filter by date range
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $auditLogs = $query->paginate(20);

        // Get unique values for filters
        $actions = AuditLog::distinct()
            ->pluck('action')
            ->sort();

        $modelTypes = AuditLog::distinct()
            ->pluck('model_type')
            ->map(fn($type) => class_basename($type))
            ->sort();

        $users = User::orderBy('name')
            ->pluck('name', 'id');

        return view('hr.audit-logs.index', compact(
            'auditLogs',
            'actions',
            'modelTypes',
            'users',
            'selectedAction',
            'selectedModel',
            'selectedUser',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Show audit log details
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');

        // Get related audit logs for the same model
        $relatedLogs = AuditLog::where('model_type', $auditLog->model_type)
            ->where('model_id', $auditLog->model_id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('hr.audit-logs.show', compact('auditLog', 'relatedLogs'));
    }

    /**
     * Get audit history for a specific model
     */
    public function modelHistory(Request $request)
    {
        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        $logs = AuditLog::where('model_type', $request->get('model_type'))
            ->where('model_id', $request->get('model_id'))
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'total' => $logs->count(),
            'logs' => $logs->map(fn($log) => [
                'id' => $log->id,
                'action' => ucfirst($log->action),
                'user' => $log->user?->name ?? 'System',
                'timestamp' => $log->created_at->diffForHumans(),
                'description' => $log->description,
                'changes' => [
                    'before' => $log->old_values,
                    'after' => $log->new_values,
                ],
            ]),
        ]);
    }

    /**
     * Get user activity log
     */
    public function userActivity(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $days = $request->integer('days', 7);
        $dateFrom = now()->subDays($days);

        $logs = AuditLog::where('user_id', $request->integer('user_id'))
            ->where('created_at', '>=', $dateFrom)
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('hr.audit-logs.user-activity', compact('logs', 'days'));
    }

    /**
     * Export audit logs as CSV
     */
    public function export(Request $request)
    {
        $selectedAction = $request->get('action', '');
        $selectedModel = $request->get('model', '');
        $dateFrom = $request->get('from_date', '');
        $dateTo = $request->get('to_date', '');

        $query = AuditLog::with('user');

        if ($selectedAction) {
            $query->where('action', $selectedAction);
        }
        if ($selectedModel) {
            $query->where('model_type', 'like', '%' . $selectedModel . '%');
        }
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $logs = $query->orderByDesc('created_at')->get();

        if ($logs->isEmpty()) {
            return redirect()->back()->withErrors(['error' => 'No audit logs found for export']);
        }

        $fileName = 'audit-logs-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        $callback = function () use ($logs) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date/Time', 'User', 'Action', 'Model', 'Model ID', 'Description', 'IP Address']);

            foreach ($logs as $log) {
                fputcsv($out, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user?->name ?? 'System',
                    ucfirst($log->action),
                    class_basename($log->model_type),
                    $log->model_id,
                    $log->description ?? '',
                    $log->ip_address ?? '',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get statistics dashboard
     */
    public function statistics(Request $request)
    {
        $days = $request->integer('days', 30);
        $dateFrom = now()->subDays($days);

        // Total logs in period
        $totalLogs = AuditLog::where('created_at', '>=', $dateFrom)->count();

        // Logs by action
        $byAction = AuditLog::where('created_at', '>=', $dateFrom)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();

        // Logs by user
        $byUser = AuditLog::where('created_at', '>=', $dateFrom)
            ->with('user')
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->get()
            ->mapWithKeys(fn($item) => [$item->user?->name ?? 'System' => $item->count])
            ->sortByDesc(fn($v) => $v)
            ->take(10)
            ->toArray();

        // Most modified models
        $byModel = AuditLog::where('created_at', '>=', $dateFrom)
            ->selectRaw('model_type, COUNT(*) as count')
            ->groupBy('model_type')
            ->get()
            ->mapWithKeys(fn($item) => [class_basename($item->model_type) => $item->count])
            ->sortByDesc(fn($v) => $v)
            ->toArray();

        return response()->json([
            'total_logs' => $totalLogs,
            'by_action' => $byAction,
            'by_user' => $byUser,
            'by_model' => $byModel,
            'period_days' => $days,
        ]);
    }

    /**
     * Purge old audit logs (Super Admin only)
     */
    public function purge(Request $request)
    {
        $request->validate([
            'days_old' => 'required|integer|min:30|max:365',
        ]);

        $daysOld = $request->integer('days_old');
        $dateCutoff = now()->subDays($daysOld);

        $deleted = AuditLog::where('created_at', '<', $dateCutoff)->delete();

        return redirect()
            ->back()
            ->with('success', "Deleted {$deleted} audit logs older than {$daysOld} days");
    }
}

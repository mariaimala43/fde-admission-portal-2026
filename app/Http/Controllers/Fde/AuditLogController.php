<?php

// SAVE AS: app/Http/Controllers/Fde/AuditLogController.php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use App\Models\AdmissionMonitoringAudit;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    // ── Index — paginated, filterable audit log ───────────────────────
    public function index(Request $request)
    {
        $query = AdmissionMonitoringAudit::with([
            'changedBy',
            'monitoring.institution.sector',
        ])->orderByDesc('created_at');

        // Filters
        if ($request->filled('user_id')) {
            $query->where('changed_by', $request->user_id);
        }
        if ($request->filled('role')) {
            $query->where('role_at_time', $request->role);
        }
        if ($request->filled('field')) {
            $query->where('field_name', $request->field);
        }
        if ($request->filled('institution_id')) {
            $query->whereHas('monitoring', fn($q) =>
                $q->where('institution_id', $request->institution_id)
            );
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs         = $query->paginate(50)->withQueryString();
        $users        = User::orderBy('name')->get(['id', 'name']);
        $institutions = Institution::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $stats = (object) [
            'total_today' => AdmissionMonitoringAudit::whereDate('created_at', today())->count(),
            'total_week'  => AdmissionMonitoringAudit::where('created_at', '>=', now()->startOfWeek())->count(),
            'total_all'   => AdmissionMonitoringAudit::count(),
            'overrides'   => AdmissionMonitoringAudit::where('role_at_time', 'fde_cell')->count(),
        ];

        return view('fde.audit.index', compact(
            'logs', 'users', 'institutions', 'stats'
        ));
    }

    // ── Show single audit record ──────────────────────────────────────
    public function show(AdmissionMonitoringAudit $auditLog)
    {
        $auditLog->load(['changedBy', 'monitoring.institution.sector', 'monitoring.classModel']);

        return view('fde.audit.show', compact('auditLog'));
    }

    // ── Export as CSV ─────────────────────────────────────────────────
    public function export(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        $query = AdmissionMonitoringAudit::with([
            'changedBy',
            'monitoring.institution',
        ])->orderByDesc('created_at');

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('role')) {
            $query->where('role_at_time', $request->role);
        }
        if ($request->filled('institution_id')) {
            $query->whereHas('monitoring', fn($q) =>
                $q->where('institution_id', $request->institution_id)
            );
        }

        $logs = $query->get();

        $filename = 'audit_log_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($logs) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'Date & Time',
                'User',
                'Role',
                'Institution',
                'Field Changed',
                'Old Value',
                'New Value',
                'Reason',
                'IP Address',
            ]);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->created_at->format('d M Y H:i:s'),
                    $log->changedBy?->name ?? '—',
                    $log->role_at_time ?? '—',
                    $log->monitoring?->institution?->name ?? '—',
                    ucwords(str_replace('_', ' ', $log->field_name)),
                    $log->old_value ?? '—',
                    $log->new_value ?? '—',
                    $log->reason ?? '—',
                    $log->ip_address ?? '—',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}

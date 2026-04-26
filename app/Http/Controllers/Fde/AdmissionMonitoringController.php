<?php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\AdmissionMonitoring;
use App\Models\AdmissionMonitoringAudit;
use App\Models\DailyAdmission;
use App\Models\AcademicYear;
use App\Models\Institution;
use App\Models\Sector;

/**
 * SAVE AS: app/Http/Controllers/Fde/AdmissionMonitoringController.php
 *
 * FDE Cell can:
 *   - Dashboard with metrics across all 400+ institutions
 *   - View any school's monitoring records
 *   - Update ALL fields (test, merit, doc) with audit trail
 *   - Override doc_status to 'complete' (requires override_reason)
 *   - Edit admission_date (requires reason, fully audited)
 *   - Finalize records
 *
 * Auto-creates monitoring records for daily_admissions that don't have one.
 */
class AdmissionMonitoringController extends Controller
{
    use AuthorizesRequests;

    // ─────────────────────────────────────────────────────────────────
    //  DASHBOARD — system-wide metrics
    // ─────────────────────────────────────────────────────────────────
    public function dashboard(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $sectorId     = $request->input('sector_id');

        // Grand stats
        $stats = AdmissionMonitoring::query()
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->when($sectorId, fn($q) => $q->forSector($sectorId))
            ->selectRaw("
                COUNT(*)                                             as total,
                SUM(workflow_status = 'finalized')                  as finalized,
                SUM(workflow_status = 'doc_verification')           as doc_verification,
                SUM(workflow_status = 'merit_confirmation')         as merit_confirmation,
                SUM(workflow_status = 'test_verification')          as test_verification,
                SUM(test_status     = 'failed')                     as test_failed,
                SUM(test_status     = 'pending')                    as test_pending,
                SUM(merit_status    = 'rejected')                   as merit_rejected,
                SUM(merit_status    = 'waiting')                    as merit_waiting,
                SUM(doc_status      = 'provisional')                as doc_provisional,
                SUM(doc_status      = 'affidavit_case')             as doc_affidavit,
                SUM(doc_status      = 'complete')                   as doc_complete,
                SUM(doc_status      = 'pending')                    as doc_pending
            ")
            ->first();

        // Today's entries
        $todayStats = AdmissionMonitoring::where('admission_date', now()->toDateString())
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->when($sectorId, fn($q) => $q->forSector($sectorId))
            ->selectRaw("COUNT(*) as total, SUM(workflow_status = 'finalized') as finalized")
            ->first();

        // Sector-wise breakdown
        $sectors = Sector::with('institutions')
            ->when($sectorId, fn($q) => $q->where('id', $sectorId))
            ->get()
            ->map(function ($sector) use ($academicYear) {
                $instIds = $sector->institutions->pluck('id');

                $s = AdmissionMonitoring::whereIn('institution_id', $instIds)
                    ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
                    ->selectRaw("
                        COUNT(*)                                as total,
                        SUM(workflow_status = 'finalized')     as finalized,
                        SUM(doc_status = 'provisional')        as provisional,
                        SUM(doc_status = 'affidavit_case')     as affidavit,
                        SUM(test_status = 'failed')            as test_failed,
                        SUM(merit_status = 'rejected')         as merit_rejected
                    ")
                    ->first();

                $sector->m_total        = $s->total        ?? 0;
                $sector->m_finalized    = $s->finalized    ?? 0;
                $sector->m_provisional  = $s->provisional  ?? 0;
                $sector->m_affidavit    = $s->affidavit    ?? 0;
                $sector->m_test_failed  = $s->test_failed  ?? 0;
                $sector->m_merit_rej    = $s->merit_rejected ?? 0;
                $sector->finalize_pct   = $sector->m_total > 0
                    ? round(($sector->m_finalized / $sector->m_total) * 100)
                    : 0;

                return $sector;
            });

        // Recent audit activity (last 20)
        $recentAudits = AdmissionMonitoringAudit::with(['changedBy', 'monitoring.institution'])
            ->latest()
            ->limit(20)
            ->get();

        $allSectors = Sector::orderBy('name')->get(['id', 'name']);

        return view('fde.monitoring.dashboard', compact(
            'stats', 'todayStats', 'sectors', 'recentAudits',
            'academicYear', 'sectorId', 'allSectors'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  LIST — all institutions or filtered
    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $academicYear  = AcademicYear::where('is_active', true)->first();
        $sectorId      = $request->input('sector_id');
        $institutionId = $request->input('institution_id');
        $workflow      = $request->input('workflow');
        $docStatus     = $request->input('doc_status');
        $testStatus    = $request->input('test_status');
        $meritStatus   = $request->input('merit_status');
        $dateFrom      = $request->input('date_from');
        $dateTo        = $request->input('date_to');
        $search        = $request->input('search');

        $records = AdmissionMonitoring::with(['institution.sector', 'classModel'])
            ->when($academicYear,  fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->when($sectorId,      fn($q) => $q->forSector($sectorId))
            ->when($institutionId, fn($q) => $q->where('institution_id', $institutionId))
            ->when($workflow,      fn($q) => $q->where('workflow_status', $workflow))
            ->when($docStatus,     fn($q) => $q->where('doc_status', $docStatus))
            ->when($testStatus,    fn($q) => $q->where('test_status', $testStatus))
            ->when($meritStatus,   fn($q) => $q->where('merit_status', $meritStatus))
            ->when($dateFrom,      fn($q) => $q->whereDate('admission_date', '>=', $dateFrom))
            ->when($dateTo,        fn($q) => $q->whereDate('admission_date', '<=', $dateTo))
            ->when($search,        fn($q) => $q->whereHas('institution', fn($q2) =>
                $q2->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")
            ))
            ->orderByDesc('admission_date')
            ->paginate(30)
            ->withQueryString();

        $sectors      = Sector::orderBy('name')->get(['id', 'name']);
        $institutions = Institution::when($sectorId, fn($q) => $q->where('sector_id', $sectorId))
            ->orderBy('name')->get(['id', 'name', 'code']);

        return view('fde.monitoring.index', compact(
            'records', 'sectors', 'institutions', 'academicYear',
            'sectorId', 'institutionId', 'workflow', 'docStatus', 'testStatus',
            'meritStatus', 'dateFrom', 'dateTo', 'search'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  SHOW — with full audit trail
    // ─────────────────────────────────────────────────────────────────
    public function show(AdmissionMonitoring $monitoring)
    {
        $monitoring->load([
            'institution.sector',
            'classModel',
            'dailyAdmission',
            'audits.changedBy',
            'testUpdatedBy',
            'meritUpdatedBy',
            'docUpdatedBy',
            'docOverrideBy',
            'finalizedBy',
        ]);

        return view('fde.monitoring.show', compact('monitoring'));
    }

    // ─────────────────────────────────────────────────────────────────
    //  UPDATE TEST STATUS (FDE full access)
    // ─────────────────────────────────────────────────────────────────
    public function updateTestStatus(Request $request, AdmissionMonitoring $monitoring)
    {
        $request->validate([
            'test_status' => 'required|in:not_required,pending,passed,failed',
            'reason'      => 'nullable|string|max:500',
        ]);

        $old = $monitoring->test_status;
        $new = $request->test_status;

        DB::transaction(function () use ($monitoring, $old, $new, $request) {
            $monitoring->test_status     = $new;
            $monitoring->test_updated_at = now();
            $monitoring->test_updated_by = Auth::id();
            $monitoring->workflow_status  = $monitoring->computeWorkflowStatus();
            $monitoring->save();

            $monitoring->logAudit('test_status', $old, $new, $request->reason);
        });

        return back()->with('success', 'Test status updated.');
    }

    // ─────────────────────────────────────────────────────────────────
    //  UPDATE MERIT STATUS (FDE only)
    // ─────────────────────────────────────────────────────────────────
    public function updateMeritStatus(Request $request, AdmissionMonitoring $monitoring)
    {
        $request->validate([
            'merit_status' => 'required|in:pending,selected,waiting,rejected',
            'reason'       => 'nullable|string|max:500',
        ]);

        // Cannot update merit if already finalized
        abort_if($monitoring->isFinalized(), 422, 'Record is already finalized.');

        $old = $monitoring->merit_status;
        $new = $request->merit_status;

        DB::transaction(function () use ($monitoring, $old, $new, $request) {
            $monitoring->merit_status     = $new;
            $monitoring->merit_updated_at = now();
            $monitoring->merit_updated_by = Auth::id();
            $monitoring->workflow_status   = $monitoring->computeWorkflowStatus();
            $monitoring->save();

            $monitoring->logAudit('merit_status', $old, $new, $request->reason);
        });

        return back()->with('success', 'Merit status updated.');
    }

    // ─────────────────────────────────────────────────────────────────
    //  OVERRIDE DOC STATUS (FDE — can set 'complete', requires reason)
    // ─────────────────────────────────────────────────────────────────
    public function overrideDocStatus(Request $request, AdmissionMonitoring $monitoring)
    {
        $request->validate([
            'doc_status'      => 'required|in:pending,provisional,affidavit_case,complete',
            'override_reason' => 'required_if:doc_status,complete|nullable|string|min:10|max:500',
            'affidavit'       => 'required_if:doc_status,affidavit_case|nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Business rules for setting 'complete'
        if ($request->doc_status === 'complete') {
            if (! $monitoring->canCompleteDoc()) {
                $reason = $monitoring->test_status === 'failed'
                    ? 'Admission test was failed.'
                    : 'Merit list must show Selected before documentation can be Complete.';
                return back()->withErrors(['doc_status' => "Cannot set Complete: {$reason}"]);
            }
        }

        $old = $monitoring->doc_status;
        $new = $request->doc_status;

        DB::transaction(function () use ($monitoring, $request, $old, $new) {
            $affidavitPath         = $monitoring->affidavit_path;
            $affidavitOriginalName = $monitoring->affidavit_original_name;

            if ($new === 'affidavit_case' && $request->hasFile('affidavit')) {
                if ($affidavitPath && Storage::disk('local')->exists($affidavitPath)) {
                    Storage::disk('local')->delete($affidavitPath);
                }
                $file                  = $request->file('affidavit');
                $affidavitOriginalName = $file->getClientOriginalName();
                $affidavitPath         = $file->store(
                    'affidavits/' . $monitoring->institution_id . '/' . now()->year,
                    'local'
                );
            }

            $isComplete = ($new === 'complete');

            $monitoring->doc_status             = $new;
            $monitoring->affidavit_path         = $affidavitPath;
            $monitoring->affidavit_original_name = $affidavitOriginalName;
            $monitoring->doc_updated_at         = now();
            $monitoring->doc_updated_by         = Auth::id();
            $monitoring->doc_override_by        = $isComplete ? Auth::id() : null;
            $monitoring->doc_override_reason    = $isComplete ? $request->override_reason : null;
            $monitoring->doc_override_at        = $isComplete ? now() : null;
            $monitoring->workflow_status         = $monitoring->computeWorkflowStatus();

            // Auto-finalize when complete
            if ($isComplete && $monitoring->workflow_status === 'finalized') {
                $monitoring->finalized_at = now();
                $monitoring->finalized_by = Auth::id();
            }

            $monitoring->save();

            $monitoring->logAudit('doc_status', $old, $new, $request->override_reason ?? 'FDE override');
        });

        return back()->with('success', 'Documentation status updated.');
    }

    // ─────────────────────────────────────────────────────────────────
    //  AUTO-CREATE monitoring records for admissions that lack one
    //  Called from dashboard sync button
    // ─────────────────────────────────────────────────────────────────
    public function sync(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->firstOrFail();

        // Use withTrashed() so soft-deleted monitoring records also block re-creation.
        // Without withTrashed(), a deleted monitoring record's daily_admission_id would
        // fall back into the NOT IN list, creating a duplicate on the next sync.
        $existingIds = AdmissionMonitoring::withTrashed()
            ->whereNotNull('daily_admission_id')
            ->pluck('daily_admission_id');

        $admissions = DailyAdmission::where('academic_year_id', $academicYear->id)
            ->whereNotIn('id', $existingIds)
            ->get();

        $created = 0;
        foreach ($admissions as $adm) {
            AdmissionMonitoring::create([
                'daily_admission_id' => $adm->id,
                'institution_id'     => $adm->institution_id,
                'class_id'           => $adm->class_id,
                'academic_year_id'   => $adm->academic_year_id,
                'admission_date'     => $adm->admission_date,
                'workflow_status'    => 'test_verification',
                'test_status'        => 'pending',
                'merit_status'       => 'pending',
                'doc_status'         => 'pending',
            ]);
            $created++;
        }

        return back()->with('success', "{$created} monitoring records created.");
    }
}

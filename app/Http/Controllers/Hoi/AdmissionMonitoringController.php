<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\AdmissionMonitoring;
use App\Models\DailyAdmission;
use App\Models\AcademicYear;

/**
 * SAVE AS: app/Http/Controllers/Hoi/AdmissionMonitoringController.php
 *
 * HOI can:
 *   - View monitoring records for their own school
 *   - Update test_status (not_required | pending | passed | failed)
 *   - Update doc_status  (pending | provisional | affidavit_case)
 *   - Upload affidavit file when doc_status = affidavit_case
 *
 * HOI CANNOT:
 *   - Set doc_status = complete (FDE only)
 *   - Change merit_status (FDE only)
 *   - Edit admission_date
 */
class AdmissionMonitoringController extends Controller
{
    use AuthorizesRequests;

    // ─────────────────────────────────────────────────────────────────
    //  LIST — own school's monitoring records
    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $institution  = Auth::user()->institution;
        abort_if(! $institution, 403, 'No institution assigned.');

        $academicYear = AcademicYear::where('is_active', true)->first();
        $workflow     = $request->input('workflow');
        $docStatus    = $request->input('doc_status');

        $records = AdmissionMonitoring::with(['classModel', 'dailyAdmission'])
            ->where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->when($workflow,  fn($q) => $q->where('workflow_status', $workflow))
            ->when($docStatus, fn($q) => $q->where('doc_status', $docStatus))
            ->orderByDesc('admission_date')
            ->paginate(25)
            ->withQueryString();

        // Summary counts for tabs
        $stats = AdmissionMonitoring::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->selectRaw("
                COUNT(*)                                        as total,
                SUM(workflow_status = 'finalized')             as finalized,
                SUM(workflow_status = 'doc_verification')      as doc_pending,
                SUM(doc_status = 'provisional')                as provisional,
                SUM(doc_status = 'affidavit_case')             as affidavit,
                SUM(test_status = 'failed')                    as test_failed
            ")
            ->first();

        return view('hoi.monitoring.index', compact('records', 'stats', 'institution', 'academicYear', 'workflow', 'docStatus'));
    }

    // ─────────────────────────────────────────────────────────────────
    //  SHOW — single record with audit trail
    // ─────────────────────────────────────────────────────────────────
    public function show(AdmissionMonitoring $monitoring)
    {
        abort_if($monitoring->institution_id !== Auth::user()->institution_id, 403);

        $monitoring->load(['classModel', 'dailyAdmission', 'audits.changedBy']);

        return view('hoi.monitoring.show', compact('monitoring'));
    }

    // ─────────────────────────────────────────────────────────────────
    //  UPDATE TEST STATUS
    // ─────────────────────────────────────────────────────────────────
    public function updateTestStatus(Request $request, AdmissionMonitoring $monitoring)
    {
        abort_if($monitoring->institution_id !== Auth::user()->institution_id, 403);
        abort_if($monitoring->isFinalized(), 422, 'Record is finalized.');
        abort_if($monitoring->isBlocked(),   422, 'Merit rejected — processing blocked.');

        $request->validate([
            'test_status' => 'required|in:not_required,pending,passed,failed',
        ]);

        $old = $monitoring->test_status;
        $new = $request->test_status;

        if ($old === $new) {
            return back()->with('info', 'No change made.');
        }

        DB::transaction(function () use ($monitoring, $old, $new) {
            $monitoring->test_updated_at = now();
            $monitoring->test_updated_by = Auth::id();
            $monitoring->test_status     = $new;
            $monitoring->workflow_status  = $monitoring->computeWorkflowStatus();
            $monitoring->save();

            $monitoring->logAudit('test_status', $old, $new);
        });

        return back()->with('success', 'Test status updated.');
    }

    // ─────────────────────────────────────────────────────────────────
    //  UPDATE DOC STATUS  (HOI cannot set 'complete')
    // ─────────────────────────────────────────────────────────────────
    public function updateDocStatus(Request $request, AdmissionMonitoring $monitoring)
    {
        abort_if($monitoring->institution_id !== Auth::user()->institution_id, 403);
        abort_if($monitoring->isFinalized(), 422, 'Record is finalized.');
        abort_if($monitoring->isBlocked(),   422, 'Merit rejected — processing blocked.');

        $request->validate([
            'doc_status'  => 'required|in:pending,provisional,affidavit_case',
            // HOI cannot set 'complete' — validated by the in: list above
            'affidavit'   => 'required_if:doc_status,affidavit_case|nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Business rule: test must not be failed
        if ($monitoring->test_status === 'failed' && $request->doc_status !== 'pending') {
            return back()->withErrors(['doc_status' => 'Cannot update documentation — admission test was failed.']);
        }

        $old = $monitoring->doc_status;
        $new = $request->doc_status;

        DB::transaction(function () use ($monitoring, $request, $old, $new) {
            $affidavitPath         = $monitoring->affidavit_path;
            $affidavitOriginalName = $monitoring->affidavit_original_name;

            // Handle file upload for affidavit case
            if ($new === 'affidavit_case' && $request->hasFile('affidavit')) {
                // Delete old file if exists
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

            $monitoring->doc_status            = $new;
            $monitoring->affidavit_path        = $affidavitPath;
            $monitoring->affidavit_original_name = $affidavitOriginalName;
            $monitoring->doc_updated_at        = now();
            $monitoring->doc_updated_by        = Auth::id();
            $monitoring->workflow_status        = $monitoring->computeWorkflowStatus();
            $monitoring->save();

            $monitoring->logAudit('doc_status', $old, $new);

            if ($new === 'affidavit_case' && $affidavitPath) {
                $monitoring->logAudit('affidavit_path', null, $affidavitOriginalName, 'File uploaded by HOI');
            }
        });

        return back()->with('success', 'Documentation status updated.');
    }
}

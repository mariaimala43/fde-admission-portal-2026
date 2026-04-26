<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\AdmissionMonitoring;
use App\Models\AdmissionMonitoringSplit;
use App\Models\DailyAdmission;
use App\Models\AcademicYear;

/**
 * SAVE AS: app/Http/Controllers/Hoi/AdmissionMonitoringController.php
 *
 * HOI is the SOLE data-entry actor for the entire monitoring workflow.
 * FDE is read-only (can view but cannot edit anything).
 *
 * HOI can:
 *   - View monitoring records for their own school
 *   - Enter test mode + counts (passed / failed / exempted)
 *   - Update doc_status per split (all values including 'complete')
 *   - Upload affidavit file when doc_status = affidavit_case
 *
 * HOI CANNOT:
 *   - View or edit other schools' records
 *   - Edit test counts once locked (test_entry_locked = true)
 *
 * Auto-finalize rules:
 *   test_mode = 'not_required'  → all exempted, doc = not_required → immediately finalized
 *   test_mode = 'required'      → no exempted split; all-passed → immediately finalized
 *   test_mode = 'mixed'         → exempted split needs doc check; passed auto-finalized
 *   any mode with failed > 0    → partial_finalized, failed group pending re-test
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

        $records = AdmissionMonitoring::with(['classModel', 'dailyAdmission', 'splits'])
            ->where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->when($workflow,  fn($q) => $q->where('workflow_status', $workflow))
            ->when($docStatus, fn($q) => $q->where('doc_status', $docStatus))
            ->orderByDesc('admission_date')
            ->paginate(25)
            ->withQueryString();

        // Summary counts for stats cards
        $stats = AdmissionMonitoring::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->selectRaw("
                COUNT(*)                                             as total,
                SUM(workflow_status = 'finalized')                  as finalized,
                SUM(workflow_status = 'partial_finalized')          as partial_finalized,
                SUM(workflow_status = 'doc_verification')           as doc_pending,
                SUM(doc_status = 'provisional')                     as provisional,
                SUM(doc_status = 'affidavit_case')                  as affidavit,
                SUM(test_entry_locked = 0 AND workflow_status != 'finalized') as test_pending,
                SUM(COALESCE(failed_count, 0))                      as total_failed_students,
                SUM(COALESCE(passed_count, 0))                      as total_passed_students
            ")
            ->first();

        return view('hoi.monitoring.index', compact(
            'records', 'stats', 'institution', 'academicYear', 'workflow', 'docStatus'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  SHOW — single record with splits + audit trail
    // ─────────────────────────────────────────────────────────────────
    public function show(AdmissionMonitoring $monitoring)
    {
        abort_if($monitoring->institution_id !== Auth::user()->institution_id, 403);

        $monitoring->load(['classModel', 'dailyAdmission', 'splits', 'audits.changedBy']);

        // Backfill total_admitted for records created before the count columns were added
        if ($monitoring->total_admitted <= 0 && $monitoring->dailyAdmission) {
            $monitoring->total_admitted = $monitoring->dailyAdmission->regularTotal();
            $monitoring->saveQuietly();
        }

        return view('hoi.monitoring.show', compact('monitoring'));
    }

    // ─────────────────────────────────────────────────────────────────
    //  SAVE TEST COUNTS
    //
    //  HOI selects a test_mode (required | not_required | mixed) and
    //  enters the corresponding counts. Sum must equal total_admitted.
    //  Locked after first save — cannot be changed without FDE override.
    //
    //  test_mode = 'not_required'
    //    All students are admitted without test (e.g. Class 1–5).
    //    Sets exempted = total, doc = not_required, finalizes immediately.
    //
    //  test_mode = 'required'
    //    Test was taken. Only passed + failed counts (exempted forced = 0).
    //    All-passed → immediately finalized. Any failed → partial_finalized.
    //
    //  test_mode = 'mixed'
    //    Some tested (passed/failed), some exempted. All 3 counts used.
    //    Exempted split goes to pending_doc for HOI to complete later.
    // ─────────────────────────────────────────────────────────────────
    public function updateTestStatus(Request $request, AdmissionMonitoring $monitoring)
    {
        abort_if($monitoring->institution_id !== Auth::user()->institution_id, 403);
        abort_if($monitoring->isFinalized(),     422, 'Record is finalized.');
        abort_if($monitoring->isBlocked(),       422, 'Record is blocked.');
        abort_if($monitoring->test_entry_locked, 422, 'Test counts are locked.');

        // ── Backfill total_admitted for rows created before this feature ──
        if ($monitoring->total_admitted <= 0 && $monitoring->dailyAdmission) {
            $monitoring->total_admitted = $monitoring->dailyAdmission->regularTotal();
            $monitoring->saveQuietly();
        }

        if ($monitoring->total_admitted <= 0) {
            return back()->withErrors([
                'passed_count' => 'Cannot determine total admitted students. Please contact support.',
            ]);
        }

        $total = $monitoring->total_admitted;

        $request->validate([
            'test_mode'      => 'required|in:required,not_required,mixed',
            'passed_count'   => 'required|integer|min:0|max:' . $total,
            'failed_count'   => 'required|integer|min:0|max:' . $total,
            'exempted_count' => 'required|integer|min:0|max:' . $total,
        ]);

        $mode     = $request->test_mode;
        $passed   = (int) $request->passed_count;
        $failed   = (int) $request->failed_count;
        $exempted = (int) $request->exempted_count;

        // ── Enforce correct counts per mode ───────────────────────────────
        if ($mode === 'not_required') {
            // Override whatever the frontend sent — all students are exempted
            $passed   = 0;
            $failed   = 0;
            $exempted = $total;
        } elseif ($mode === 'required') {
            // No exempted students when test is required
            $exempted = 0;
        }

        // ── Sum validation ────────────────────────────────────────────────
        if (! $monitoring->countsAreValid($passed, $failed, $exempted)) {
            return back()->withErrors([
                'passed_count' => "Counts must sum to {$total} (total admitted). Got " . ($passed + $failed + $exempted) . '.',
            ])->withInput();
        }

        DB::transaction(function () use ($monitoring, $mode, $passed, $failed, $exempted) {
            $userId = Auth::id();
            $now    = now();

            // ── 1. Save counts on parent ──────────────────────────────────
            $monitoring->passed_count    = $passed;
            $monitoring->failed_count    = $failed;
            $monitoring->exempted_count  = $exempted;
            $monitoring->test_updated_at = $now;
            $monitoring->test_updated_by = $userId;

            // ── 2. Derive legacy test_status field ────────────────────────
            if ($mode === 'not_required') {
                $monitoring->test_status = 'not_required';
            } elseif ($failed === 0 && $exempted === 0) {
                $monitoring->test_status = 'passed';
            } elseif ($passed === 0 && $exempted === 0) {
                $monitoring->test_status = 'failed';
            } elseif ($failed === 0) {
                $monitoring->test_status = 'not_required';
            } else {
                $monitoring->test_status = 'pending'; // mixed with some failures
            }

            // ── 3. Auto-set merit_status when no failures ─────────────────
            if ($failed === 0) {
                $monitoring->merit_status = 'selected'; // all students confirmed
            }

            // ── 4. Build splits ───────────────────────────────────────────
            $monitoring->splits()->delete(); // clean up any prior partial saves

            if ($mode === 'not_required') {
                // Test not required → exempted split finalized immediately
                AdmissionMonitoringSplit::create([
                    'monitoring_id'   => $monitoring->id,
                    'split_type'      => 'exempted',
                    'student_count'   => $exempted,
                    'workflow_status' => 'finalized',   // immediate
                    'doc_status'      => 'not_required',// no docs needed
                    'finalized_at'    => $now,
                    'created_by'      => $userId,
                    'updated_by'      => $userId,
                ]);
            } else {
                // Normal test or mixed

                if ($passed > 0) {
                    // Passed → auto-finalized, no doc check
                    AdmissionMonitoringSplit::create([
                        'monitoring_id'   => $monitoring->id,
                        'split_type'      => 'passed',
                        'student_count'   => $passed,
                        'workflow_status' => 'finalized',
                        'doc_status'      => null,
                        'finalized_at'    => $now,
                        'created_by'      => $userId,
                        'updated_by'      => $userId,
                    ]);
                }

                if ($exempted > 0) {
                    // Exempted (mixed mode) → needs doc check
                    AdmissionMonitoringSplit::create([
                        'monitoring_id'   => $monitoring->id,
                        'split_type'      => 'exempted',
                        'student_count'   => $exempted,
                        'workflow_status' => 'pending_doc',
                        'doc_status'      => 'pending',
                        'created_by'      => $userId,
                        'updated_by'      => $userId,
                    ]);
                }

                if ($failed > 0) {
                    // Failed → informational, re-test via new DailyAdmission
                    AdmissionMonitoringSplit::create([
                        'monitoring_id'   => $monitoring->id,
                        'split_type'      => 'failed',
                        'student_count'   => $failed,
                        'workflow_status' => 'pending_retest',
                        'doc_status'      => null,
                        'created_by'      => $userId,
                        'updated_by'      => $userId,
                    ]);
                }
            }

            // ── 5. Lock test counts ───────────────────────────────────────
            $monitoring->test_entry_locked = true;

            // ── 6. Partial finalized flag ─────────────────────────────────
            $monitoring->partial_finalized = ($failed > 0 && ($passed > 0 || $exempted > 0));

            // ── 7. Auto-finalize timestamp when no failures ───────────────
            if ($failed === 0) {
                $monitoring->auto_finalized_at = $now;
            }

            // ── 8. Compute parent workflow_status ─────────────────────────
            $monitoring->load('splits');
            $monitoring->workflow_status = $monitoring->computeWorkflowStatus();

            // Finalize parent when all splits are done
            if ($mode === 'not_required' || ($monitoring->canAutoFinalize() && $exempted === 0)) {
                $monitoring->finalized_at = $now;
                $monitoring->finalized_by = $userId;
                $monitoring->workflow_status = 'finalized';
            }

            $monitoring->save();

            // ── 9. Audit ──────────────────────────────────────────────────
            $monitoring->logAudit(
                'test_counts',
                null,
                "mode={$mode}, passed={$passed}, failed={$failed}, exempted={$exempted}",
                'Test results entered by HOI'
            );

            if ($failed === 0) {
                $monitoring->logAudit(
                    'workflow_status',
                    'test_verification',
                    $monitoring->workflow_status,
                    'Auto-finalized — all students admitted'
                );
            }
        });

        $message = match(true) {
            $mode === 'not_required'    => '✅ Saved — all students marked as Test Not Required and finalized.',
            $failed === 0               => '✅ Test results saved — all ' . $passed . ' students passed and finalized.',
            $passed > 0 || $exempted > 0 => "✅ Saved — {$passed} passed (finalized), {$failed} failed (pending re-test).",
            default                     => '✅ Test results saved.',
        };

        return back()->with('success', $message);
    }

    // ─────────────────────────────────────────────────────────────────
    //  UPDATE DOC STATUS — operates on a specific (exempted) split
    //
    //  HOI can set any doc_status including 'complete'.
    //  'complete' finalizes the split and, if all splits done, the parent.
    //  'not_required' also finalizes the split immediately.
    //  Other values (pending / provisional / affidavit_case) keep it open.
    // ─────────────────────────────────────────────────────────────────
    public function updateDocStatus(Request $request, AdmissionMonitoring $monitoring)
    {
        abort_if($monitoring->institution_id !== Auth::user()->institution_id, 403);
        abort_if($monitoring->isFinalized(), 422, 'Record is finalized.');
        abort_if($monitoring->isBlocked(),   422, 'Record is blocked.');

        $request->validate([
            'split_id'   => 'required|integer|exists:admission_monitoring_splits,id',
            'doc_status' => 'required|in:not_required,pending,provisional,affidavit_case,complete',
            'affidavit'  => 'required_if:doc_status,affidavit_case|nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $split = AdmissionMonitoringSplit::where('id', $request->split_id)
            ->where('monitoring_id', $monitoring->id)
            ->firstOrFail();

        abort_if(
            $split->split_type !== 'exempted',
            422,
            'Document status can only be updated for exempted students.'
        );

        $old = $split->doc_status;
        $new = $request->doc_status;

        DB::transaction(function () use ($monitoring, $split, $request, $old, $new) {
            $userId = Auth::id();
            $now    = now();

            $affidavitPath         = $split->affidavit_path;
            $affidavitOriginalName = $split->affidavit_original_name;

            // Handle affidavit file upload
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

            // Determine new split workflow_status
            $finalizeNow = in_array($new, ['not_required', 'complete']);

            $split->doc_status              = $new;
            $split->affidavit_path          = $affidavitPath;
            $split->affidavit_original_name = $affidavitOriginalName;
            $split->doc_updated_at          = $now;
            $split->doc_updated_by          = $userId;
            $split->updated_by              = $userId;
            $split->workflow_status         = $finalizeNow ? 'doc_complete' : 'pending_doc';

            if ($finalizeNow) {
                $split->finalized_at = $now;
            }

            $split->save();

            // Re-check if all actionable splits are now finalized
            $monitoring->load('splits');
            $monitoring->workflow_status = $monitoring->computeWorkflowStatus();

            if ($monitoring->allSplitsFinalized()) {
                $monitoring->finalized_at    = $now;
                $monitoring->finalized_by    = $userId;
                $monitoring->workflow_status = 'finalized';
            }

            $monitoring->save();

            // Audit
            $monitoring->logAudit(
                'doc_status',
                $old,
                $new,
                "Split [{$split->split_type}] doc status updated by HOI"
            );

            if ($new === 'affidavit_case' && $affidavitPath) {
                $monitoring->logAudit(
                    'affidavit_path',
                    null,
                    $affidavitOriginalName,
                    "Affidavit uploaded by HOI"
                );
            }
        });

        $messages = [
            'not_required'   => '✅ Marked as Not Required — batch finalized.',
            'complete'       => '✅ Documents marked as Complete — batch finalized.',
            'pending'        => '📋 Document status set to Pending.',
            'provisional'    => '📄 Provisional admission recorded.',
            'affidavit_case' => '📎 Affidavit case recorded.',
        ];

        return back()->with('success', $messages[$new] ?? 'Documentation status updated.');
    }
}

<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\Referral;
use App\Models\DailyAdmission;
use App\Models\InstitutionClass;
use App\Models\AcademicYear;

/**
 * SAVE AS: app/Http/Controllers/Hoi/ReferralController.php
 *
 * HOI can:
 *   - View all referrals sent to their school
 *   - Accept a pending referral → auto-creates daily admission for today
 *   - Reject a pending referral with a mandatory reason
 */
class ReferralController extends Controller
{
    use AuthorizesRequests;

    // ─────────────────────────────────────────────────────────────────
    //  LIST REFERRALS FOR THIS SCHOOL
    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $user        = Auth::user();
        $institution = $user->institution;

        abort_if(! $institution, 403, 'No institution assigned.');

        $status  = $request->input('status', 'pending');
        $classId = $request->input('class_id');

        $referrals = Referral::with(['classModel', 'referredBy'])
            ->where('institution_id', $institution->id)
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Stats for tabs
        $stats = Referral::where('institution_id', $institution->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'pending')  as pending,
                SUM(status = 'accepted') as accepted,
                SUM(status = 'rejected') as rejected,
                SUM(status = 'closed')   as closed
            ")
            ->first();

        $classes = Classes::where('is_active', true)->orderBy('order')->get(['id', 'name']);

        return view('hoi.referrals.index', compact('referrals', 'stats', 'status', 'institution', 'classes'));
    }

    // ─────────────────────────────────────────────────────────────────
    //  ACCEPT — auto-creates/increments daily admission for today
    // ─────────────────────────────────────────────────────────────────
    public function accept(Request $request, Referral $referral)
    {
        $user        = Auth::user();
        $institution = $user->institution;

        // Security: HOI can only act on referrals for their own school
        abort_if($referral->institution_id !== $institution->id, 403);
        abort_if(! $referral->isPending(), 422, 'This referral is no longer pending.');

        $request->validate([
            'shift'  => 'required|in:morning,evening',
            'gender' => 'required|in:male,female',
        ]);

        abort_if(! $referral->class_id, 422, 'Referral has no class assigned. Ask FDE to update it first.');

        $academicYear = AcademicYear::where('is_active', true)->firstOrFail();
        $today        = now()->toDateString();

        // Verify class is configured for this institution
        $instClass = InstitutionClass::where('institution_id', $institution->id)
            ->where('class_id', $referral->class_id)
            ->where('is_active', true)
            ->first();

        abort_if(! $instClass, 422, 'This class is not configured for your school.');

        DB::transaction(function () use ($referral, $request, $institution, $academicYear, $today, $instClass) {

            // Determine which column to increment
            // shift × gender → column name
            $column = match (true) {
                $request->shift === 'morning' && $request->gender === 'male'   => 'morning_boys',
                $request->shift === 'morning' && $request->gender === 'female' => 'morning_girls',
                $request->shift === 'evening' && $request->gender === 'male'   => 'evening_boys',
                $request->shift === 'evening' && $request->gender === 'female' => 'evening_girls',
            };

            // Find existing today's admission for this class or create one
            $admission = DailyAdmission::where('institution_id', $institution->id)
                ->where('class_id', $referral->class_id)
                ->where('admission_date', $today)
                ->where('academic_year_id', $academicYear->id)
                ->first();

            if ($admission) {
                // Increment the correct column by 1
                $admission->increment($column);
                // If it was a draft, keep it as-is — HOI can submit later
                // If it was already verified, still increment (referral = FDE-authorised)
                $admission->update(['referral_id' => $referral->id]);
                $admissionId = $admission->id;
            } else {
                // Create fresh admission row for today with this referral
                $newAdmission = DailyAdmission::create([
                    'referral_id'      => $referral->id,
                    'institution_id'   => $institution->id,
                    'class_id'         => $referral->class_id,
                    'academic_year_id' => $academicYear->id,
                    'admission_date'   => $today,
                    'morning_boys'     => ($request->shift === 'morning' && $request->gender === 'male')   ? 1 : 0,
                    'morning_girls'    => ($request->shift === 'morning' && $request->gender === 'female') ? 1 : 0,
                    'evening_boys'     => ($request->shift === 'evening' && $request->gender === 'male')   ? 1 : 0,
                    'evening_girls'    => ($request->shift === 'evening' && $request->gender === 'female') ? 1 : 0,
                    'oosc_boys'        => 0,
                    'oosc_girls'       => 0,
                    'p2p_boys'         => 0,
                    'p2p_girls'        => 0,
                    'status'           => 'verified',   // referral acceptance = auto-verified
                    'submitted_by'     => Auth::id(),
                    'submitted_at'     => now(),
                    'verified_by'      => Auth::id(),
                    'verified_at'      => now(),
                ]);
                $admissionId = $newAdmission->id;
            }

            // Mark referral as accepted
            $referral->update([
                'status'             => 'accepted',
                'gender'             => $request->gender,
                'shift'              => $request->shift,
                'accepted_at'        => now(),
                'actioned_by'        => Auth::id(),
                'daily_admission_id' => $admissionId,
            ]);
        });

        return redirect()->route('hoi.referrals.index')
            ->with('success', "Referral {$referral->reference_no} accepted. Daily admission updated.");
    }

    // ─────────────────────────────────────────────────────────────────
    //  REJECT — requires a reason
    // ─────────────────────────────────────────────────────────────────
    public function reject(Request $request, Referral $referral)
    {
        $user        = Auth::user();
        $institution = $user->institution;

        abort_if($referral->institution_id !== $institution->id, 403);
        abort_if(! $referral->isPending(), 422, 'This referral is no longer pending.');

        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500',
        ]);

        $referral->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'rejected_at'      => now(),
            'actioned_by'      => Auth::id(),
        ]);

        return redirect()->route('hoi.referrals.index')
            ->with('success', "Referral {$referral->reference_no} rejected.");
    }

    // ─────────────────────────────────────────────────────────────────
    //  SHOW — detail view with tracking card
    // ─────────────────────────────────────────────────────────────────
    public function show(Referral $referral)
    {
        $institution = Auth::user()->institution;
        abort_if(! $institution, 403);
        abort_if($referral->institution_id !== $institution->id, 403);

        $referral->load(['classModel', 'referredBy', 'actionedBy', 'testUpdatedBy', 'admissionUpdatedBy']);

        return view('hoi.referrals.show', compact('referral', 'institution'));
    }

    // ─────────────────────────────────────────────────────────────────
    //  UPDATE TEST — HOI records whether test was conducted + result
    // ─────────────────────────────────────────────────────────────────
    public function updateTest(Request $request, Referral $referral)
    {
        $institution = Auth::user()->institution;
        abort_if(! $institution, 403);
        abort_if($referral->institution_id !== $institution->id, 403);
        abort_if(! $referral->canUpdateTest(), 422, 'Test details cannot be updated for this referral.');

        $request->validate([
            'test_conducted' => 'required|in:yes,no,exempted',
            'test_result'    => 'required_if:test_conducted,yes|nullable|in:pass,fail',
        ]);

        // If no test / exempted → clear the result field
        $testResult = $request->test_conducted === 'yes' ? $request->test_result : null;

        $old = [
            'test_conducted' => $referral->test_conducted,
            'test_result'    => $referral->test_result,
        ];

        $referral->update([
            'test_conducted'  => $request->test_conducted,
            'test_result'     => $testResult,
            'test_updated_at' => now(),
            'test_updated_by' => Auth::id(),
        ]);

        AuditLog::record(
            'updated',
            'Referral',
            $referral->id,
            $old,
            ['test_conducted' => $request->test_conducted, 'test_result' => $testResult],
            "Test details updated for referral {$referral->reference_no}",
            $referral->institution_id
        );

        return redirect()->route('hoi.referrals.show', $referral)
            ->with('success', 'Test details saved successfully.');
    }

    // ─────────────────────────────────────────────────────────────────
    //  UPDATE ADMISSION — HOI records final admission decision
    // ─────────────────────────────────────────────────────────────────
    public function updateAdmission(Request $request, Referral $referral)
    {
        $institution = Auth::user()->institution;
        abort_if(! $institution, 403);
        abort_if($referral->institution_id !== $institution->id, 403);
        abort_if(! $referral->canUpdateAdmission(), 422, 'Admission status cannot be updated yet. Complete the test stage first.');

        $request->validate([
            'admission_status' => 'required|in:admitted,not_admitted',
        ]);

        $old = ['admission_status' => $referral->admission_status];

        $referral->update([
            'admission_status'     => $request->admission_status,
            'admission_updated_at' => now(),
            'admission_updated_by' => Auth::id(),
        ]);

        AuditLog::record(
            'updated',
            'Referral',
            $referral->id,
            $old,
            ['admission_status' => $request->admission_status],
            "Admission decision recorded for referral {$referral->reference_no}",
            $referral->institution_id
        );

        $label = $request->admission_status === 'admitted' ? 'Admitted ✅' : 'Not Admitted';

        return redirect()->route('hoi.referrals.show', $referral)
            ->with('success', "Admission decision saved: {$label}.");
    }
}

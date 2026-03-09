<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $status = $request->input('status', 'pending');

        $referrals = Referral::with(['classModel', 'referredBy'])
            ->where('institution_id', $institution->id)
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
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

        return view('hoi.referrals.index', compact('referrals', 'stats', 'status', 'institution'));
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
}

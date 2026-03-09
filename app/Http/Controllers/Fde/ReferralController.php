<?php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Referral;
use App\Models\Institution;
use App\Models\Classes;
use App\Models\AcademicYear;
use App\Models\Sector;

/**
 * SAVE AS: app/Http/Controllers/Fde/ReferralController.php
 *
 * FDE Cell can:
 *   - Create a new referral (pick school → fill student info → submit)
 *   - Edit a PENDING referral (before HOI acts)
 *   - Cancel a PENDING referral
 *   - Re-refer a REJECTED referral to a different school
 *   - View all referrals system-wide with filters
 */
class ReferralController extends Controller
{
    use AuthorizesRequests;

    // ─────────────────────────────────────────────────────────────────
    //  LIST ALL REFERRALS
    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $status        = $request->input('status');
        $institutionId = $request->input('institution_id');
        $search        = $request->input('search');

        $referrals = Referral::with(['institution.sector', 'classModel', 'referredBy', 'actionedBy'])
            ->when($status,        fn($q) => $q->where('status', $status))
            ->when($institutionId, fn($q) => $q->where('institution_id', $institutionId))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('student_name', 'like', "%{$search}%")
                       ->orWhere('father_name',  'like', "%{$search}%")
                       ->orWhere('reference_no', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Stats for header cards
        $stats = Referral::selectRaw("
            COUNT(*) as total,
            SUM(status = 'pending')     as pending,
            SUM(status = 'accepted')    as accepted,
            SUM(status = 'rejected')    as rejected,
            SUM(status = 'closed')      as closed,
            SUM(status = 're_referred') as re_referred
        ")->first();

        $institutions = Institution::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('fde.referrals.index', compact('referrals', 'stats', 'institutions', 'status', 'search'));
    }

    // ─────────────────────────────────────────────────────────────────
    //  CREATE FORM
    // ─────────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $sectors      = Sector::with('institutions')->orderBy('name')->get();
        $classes      = Classes::where('is_active', true)->orderBy('is_ece')->orderBy('order')->get();

        // If re-referring a rejected referral, pre-fill from parent
        $parent = null;
        if ($request->input('from_referral')) {
            $parent = Referral::with(['classModel', 'institution'])
                ->where('id', $request->input('from_referral'))
                ->where('status', 'rejected')
                ->first();
        }

        return view('fde.referrals.create', compact('academicYear', 'sectors', 'classes', 'parent'));
    }

    // ─────────────────────────────────────────────────────────────────
    //  STORE NEW REFERRAL
    // ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'institution_id' => 'required|exists:institutions,id',
            'student_name'   => 'nullable|string|max:100',
            'father_name'    => 'nullable|string|max:100',
            'class_id'       => 'nullable|exists:classes,id',
            'gender'         => 'nullable|in:male,female',
            'shift'          => 'required|in:morning,evening',
            'notes'          => 'nullable|string|max:1000',
            'parent_referral_id' => 'nullable|exists:referrals,id',
        ]);

        $academicYear = AcademicYear::where('is_active', true)->firstOrFail();

        DB::transaction(function () use ($request, $academicYear) {
            $referral = Referral::create([
                'reference_no'       => Referral::generateReferenceNo(),
                'referred_by'        => Auth::id(),
                'institution_id'     => $request->institution_id,
                'academic_year_id'   => $academicYear->id,
                'student_name'       => $request->student_name,
                'father_name'        => $request->father_name,
                'class_id'           => $request->class_id,
                'gender'             => $request->gender,
                'shift'              => $request->shift,
                'notes'              => $request->notes,
                'status'             => 'pending',
                'parent_referral_id' => $request->parent_referral_id,
            ]);

            // If this was created from a rejected referral, mark parent as re_referred
            if ($request->parent_referral_id) {
                Referral::where('id', $request->parent_referral_id)
                    ->where('status', 'rejected')
                    ->update([
                        'status'        => 're_referred',
                        're_referred_to' => $referral->id,
                    ]);
            }
        });

        return redirect()->route('fde.referrals.index')
            ->with('success', 'Referral created successfully.');
    }

    // ─────────────────────────────────────────────────────────────────
    //  EDIT FORM  (pending only)
    // ─────────────────────────────────────────────────────────────────
    public function edit(Referral $referral)
    {
        abort_if(! $referral->isPending(), 403, 'Only pending referrals can be edited.');

        $sectors  = Sector::with('institutions')->orderBy('name')->get();
        $classes  = Classes::where('is_active', true)->orderBy('is_ece')->orderBy('order')->get();

        return view('fde.referrals.edit', compact('referral', 'sectors', 'classes'));
    }

    // ─────────────────────────────────────────────────────────────────
    //  UPDATE  (pending only)
    // ─────────────────────────────────────────────────────────────────
    public function update(Request $request, Referral $referral)
    {
        abort_if(! $referral->isPending(), 403, 'Only pending referrals can be edited.');

        $request->validate([
            'institution_id' => 'required|exists:institutions,id',
            'student_name'   => 'nullable|string|max:100',
            'father_name'    => 'nullable|string|max:100',
            'class_id'       => 'nullable|exists:classes,id',
            'gender'         => 'nullable|in:male,female',
            'shift'          => 'required|in:morning,evening',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $referral->update([
            'institution_id' => $request->institution_id,
            'student_name'   => $request->student_name,
            'father_name'    => $request->father_name,
            'class_id'       => $request->class_id,
            'gender'         => $request->gender,
            'shift'          => $request->shift,
            'notes'          => $request->notes,
        ]);

        return redirect()->route('fde.referrals.index')
            ->with('success', 'Referral updated successfully.');
    }

    // ─────────────────────────────────────────────────────────────────
    //  CANCEL  (pending only)
    // ─────────────────────────────────────────────────────────────────
    public function cancel(Referral $referral)
    {
        abort_if(! $referral->isPending(), 403, 'Only pending referrals can be cancelled.');

        $referral->update([
            'status'      => 'closed',
            'closed_at'   => now(),
            'actioned_by' => Auth::id(),
        ]);

        return redirect()->route('fde.referrals.index')
            ->with('success', "Referral {$referral->reference_no} has been cancelled.");
    }

    // ─────────────────────────────────────────────────────────────────
    //  SHOW DETAIL
    // ─────────────────────────────────────────────────────────────────
    public function show(Referral $referral)
    {
        $referral->load([
            'institution.sector',
            'classModel',
            'referredBy',
            'actionedBy',
            'dailyAdmission',
            'parentReferral.institution',
            'reReferredTo.institution',
        ]);

        return view('fde.referrals.show', compact('referral'));
    }
}

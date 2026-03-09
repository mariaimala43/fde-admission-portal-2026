<?php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Institution;
use App\Models\InstitutionClass;
use App\Models\InstitutionSection;
use App\Models\AcademicYear;
use App\Models\DailyAdmission;

/**
 * SAVE AS: app/Http/Controllers/Fde/EnrollmentOverrideController.php
 *
 * Add these routes inside the fde middleware group in web.php:
 *
 *   use App\Http\Controllers\Fde\EnrollmentOverrideController;
 *
 *   Route::get( 'enrollment/{institution}',         [EnrollmentOverrideController::class, 'show']  )->name('enrollment.show');
 *   Route::post('enrollment/{institution}/unlock',  [EnrollmentOverrideController::class, 'unlock'])->name('enrollment.unlock');
 *   Route::put( 'enrollment/{institution}',         [EnrollmentOverrideController::class, 'update'])->name('enrollment.update');
 */
class EnrollmentOverrideController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    //  SHOW — enrollment state for a school + override options
    // ─────────────────────────────────────────────────────────────────
    public function show(Institution $institution)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        $classes = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->with('classModel')
            ->orderBy('class_id')
            ->get();

        $sections = InstitutionSection::where('institution_id', $institution->id)
            ->selectRaw('class_id, COUNT(*) as cnt')
            ->groupBy('class_id')
            ->pluck('cnt', 'class_id');

        // Cumulative verified admissions per class (for seat calc display)
        $cumulativeAdmissions = DailyAdmission::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->whereIn('status', ['verified', 'locked'])
            ->selectRaw('class_id, SUM(morning_boys+morning_girls+evening_boys+evening_girls) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $totalSeats    = $classes->sum('total_seats');
        $totalEnrolled = $classes->sum('existing_enrollment');
        $totalAdmitted = $cumulativeAdmissions->sum();
        $totalAvail    = $classes->sum(fn($c) =>
            max(0, $c->total_seats - $c->existing_enrollment - ($cumulativeAdmissions[$c->class_id] ?? 0))
        );

        // Status summary — used to decide which action buttons to show
        $statuses        = $classes->pluck('enrollment_status')->unique();
        $anyLocked       = $statuses->contains(fn($s) => in_array($s, ['submitted', 'verified', 'locked']));
        $allEditable     = $classes->every(fn($c) => $c->isEnrollmentEditable());

        return view('fde.enrollment.show', compact(
            'institution', 'classes', 'sections', 'cumulativeAdmissions',
            'academicYear', 'totalSeats', 'totalEnrolled', 'totalAdmitted', 'totalAvail',
            'anyLocked', 'allEditable'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  UNLOCK — reset all classes back to draft so HOI can re-edit
    // ─────────────────────────────────────────────────────────────────
    public function unlock(Request $request, Institution $institution)
    {
        $request->validate([
            'override_reason' => 'required|string|min:10|max:500',
        ], [
            'override_reason.required' => 'A reason is required before unlocking enrollment.',
            'override_reason.min'      => 'Please provide a meaningful reason (at least 10 characters).',
        ]);

        $now    = now();
        $userId = Auth::id();

        DB::transaction(function () use ($institution, $request, $now, $userId) {
            InstitutionClass::where('institution_id', $institution->id)
                ->where('is_active', true)
                ->update([
                    'enrollment_status' => 'draft',
                    'overridden_by'     => $userId,
                    'override_reason'   => $request->override_reason,
                    'overridden_at'     => $now,
                ]);
        });

        return redirect()->route('fde.enrollment.show', $institution)
            ->with('success', "Enrollment for {$institution->name} has been unlocked. HOI can now re-edit and re-submit.");
    }

    // ─────────────────────────────────────────────────────────────────
    //  UPDATE — FDE directly edits enrollment figures (bypass HOI)
    // ─────────────────────────────────────────────────────────────────
    public function update(Request $request, Institution $institution)
    {
        $request->validate([
            'override_reason'             => 'required|string|min:10|max:500',
            'enrollment'                  => 'required|array',
            'enrollment.*.class_id'       => 'required|exists:classes,id',
            'enrollment.*.existing'       => 'required|integer|min:0|max:99999',
        ], [
            'override_reason.required' => 'A reason is required before editing enrollment directly.',
        ]);

        $now    = now();
        $userId = Auth::id();

        DB::transaction(function () use ($request, $institution, $now, $userId) {
            foreach ($request->input('enrollment', []) as $item) {
                InstitutionClass::where('institution_id', $institution->id)
                    ->where('class_id', $item['class_id'])
                    ->where('is_active', true)
                    ->update([
                        'existing_enrollment' => (int) $item['existing'],
                        'enrollment_status'   => 'verified',   // FDE edit = auto-verified
                        'overridden_by'       => $userId,
                        'override_reason'     => $request->override_reason,
                        'overridden_at'       => $now,
                    ]);
            }
        });

        return redirect()->route('fde.enrollment.show', $institution)
            ->with('success', "Enrollment for {$institution->name} updated directly by FDE Cell.");
    }
}

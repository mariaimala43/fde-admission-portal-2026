<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\InstitutionClass;
use App\Models\DailyAdmission;
use App\Models\AcademicYear;

class EnrollmentController extends Controller
{
    public function index()
    {
        $user        = Auth::user();
        $institution = $user->institution;

        if (!$institution) {
            return redirect()->route('hoi.profile.setup');
        }

        if (!$institution->classes_configured) {
            return redirect()->route('hoi.classes.setup')
                ->with('error', 'Please configure your classes first.');
        }

        $classes = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->with(['classModel', 'sections'])
            ->orderBy('class_id')
            ->get();

        $sections = \App\Models\InstitutionSection::where('institution_id', $institution->id)
            ->orderBy('order')
            ->get()
            ->groupBy('class_id');

        // Cumulative admissions per class for the active academic year
        $academicYear = AcademicYear::where('is_active', true)->first();
        $newlyAdmitted = DailyAdmission::where('institution_id', $institution->id)
            ->where('academic_year_id', $academicYear?->id)
            ->selectRaw('class_id, SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $totalSeats      = $classes->sum('total_seats');
        $totalEnrolled   = $classes->sum('existing_enrollment');
        $totalNewAdmit   = $newlyAdmitted->sum();
        $totalEnrollment = $totalEnrolled + $totalNewAdmit;
        $totalAvailable  = $classes->sum(fn($c) => max(0, $c->total_seats - $c->existing_enrollment - ($newlyAdmitted[$c->class_id] ?? 0)));
        $isOverCapacity  = $totalEnrollment > $totalSeats;
        $overBy          = max(0, $totalEnrollment - $totalSeats);
        $allSubmitted    = $classes->isNotEmpty()
            && $classes->every(fn($c) => in_array($c->enrollment_status, ['verified', 'locked']));

        $hasEvening = (bool) $institution->has_evening_classes;

        return view('hoi.enrollment.index', compact(
            'institution', 'classes', 'sections', 'newlyAdmitted',
            'totalSeats', 'totalEnrolled', 'totalNewAdmit', 'totalAvailable',
            'totalEnrollment', 'isOverCapacity', 'overBy', 'allSubmitted', 'hasEvening'
        ));
    }

    public function save(Request $request)
    {
        $user        = Auth::user();
        $institution = $user->institution;

        $request->validate([
            'enrollment'              => 'required|array',
            'enrollment.*.class_id'   => 'required|exists:classes,id',
            'enrollment.*.existing'   => 'nullable|integer|min:0|max:99999',
        ]);

        $action = $request->input('action', 'save'); // 'save' or 'submit'

        // ── Validate caps (per-shift for evening non-ECE, combined for others) ──
        foreach ($request->input('enrollment', []) as $item) {
            $ic = InstitutionClass::where('institution_id', $institution->id)
                    ->where('class_id', $item['class_id'])->first();
            if (!$ic) continue;
            $className = $ic->classModel?->name ?? "Class {$item['class_id']}";
            if ($institution->has_evening_classes) {
                $mTotal = (int)($item['morning_promoted'] ?? 0) + (int)($item['morning_failed'] ?? 0);
                $eTotal = (int)($item['evening_promoted'] ?? 0) + (int)($item['evening_failed'] ?? 0);
                if ($ic->morning_existing > 0 && $mTotal > $ic->morning_existing) {
                    return back()->withInput()->withErrors([
                        'enrollment' => "{$className}: Morning Promoted + Failed ({$mTotal}) cannot exceed Morning Existing ({$ic->morning_existing}).",
                    ]);
                }
                if ($ic->evening_existing > 0 && $eTotal > $ic->evening_existing) {
                    return back()->withInput()->withErrors([
                        'enrollment' => "{$className}: Evening Promoted + Failed ({$eTotal}) cannot exceed Evening Existing ({$ic->evening_existing}).",
                    ]);
                }
            } else {
                $total = (int)($item['promoted'] ?? 0) + (int)($item['failed'] ?? 0);
                if ($ic->existing_enrollment > 0 && $total > $ic->existing_enrollment) {
                    return back()->withInput()->withErrors([
                        'enrollment' => "{$className}: Promoted + Failed ({$total}) cannot exceed Existing Students ({$ic->existing_enrollment}) set in Classes & Section Setup.",
                    ]);
                }
            }
        }

        DB::transaction(function () use ($request, $institution, $action) {
            foreach ($request->input('enrollment', []) as $item) {
                $ic = InstitutionClass::where('institution_id', $institution->id)
                    ->where('class_id', $item['class_id'])
                    ->first();

                if (!$ic) continue;

                if ($institution->has_evening_classes) {
                    $mPromoted = (int) ($item['morning_promoted'] ?? 0);
                    $mFailed   = (int) ($item['morning_failed']   ?? 0);
                    $ePromoted = (int) ($item['evening_promoted'] ?? 0);
                    $eFailed   = (int) ($item['evening_failed']   ?? 0);

                    $ic->update([
                        'morning_promoted'  => $mPromoted,
                        'morning_failed'    => $mFailed,
                        'evening_promoted'  => $ePromoted,
                        'evening_failed'    => $eFailed,
                        'promoted_count'    => $mPromoted + $ePromoted,
                        'failed_count'      => $mFailed   + $eFailed,
                        'enrollment_status' => $action === 'submit' ? 'verified' : 'draft',
                    ]);
                } else {
                    $promoted = (int) ($item['promoted'] ?? 0);
                    $failed   = (int) ($item['failed']   ?? 0);

                    $ic->update([
                        'promoted_count'    => $promoted,
                        'failed_count'      => $failed,
                        'enrollment_status' => $action === 'submit' ? 'verified' : 'draft',
                    ]);
                }
            }
        });

        $message = $action === 'submit'
            ? 'Enrollment configured. Seat calculations are now active.'
            : 'Enrollment saved as draft.';

        return redirect()->route('hoi.enrollment.index')
            ->with('success', $message);
    }
}

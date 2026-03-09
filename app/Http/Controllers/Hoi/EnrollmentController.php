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
        $totalEnrollment = $totalEnrolled + $totalNewAdmit;  // combined for capacity check
        $totalAvailable  = $classes->sum(fn($c) => max(0, $c->total_seats - $c->existing_enrollment - ($newlyAdmitted[$c->class_id] ?? 0)));
        $isOverCapacity  = $totalEnrollment > $totalSeats;
        $overBy          = max(0, $totalEnrollment - $totalSeats);
        $allSubmitted    = $classes->isNotEmpty()
            && $classes->every(fn($c) => $c->enrollment_status === 'submitted');

        return view('hoi.enrollment.index', compact(
            'institution', 'classes', 'sections', 'newlyAdmitted',
            'totalSeats', 'totalEnrolled', 'totalNewAdmit', 'totalAvailable',
            'totalEnrollment', 'isOverCapacity', 'overBy', 'allSubmitted'
        ));
    }

    public function save(Request $request)
    {
        $user        = Auth::user();
        $institution = $user->institution;

        $request->validate([
            'enrollment'              => 'required|array',
            'enrollment.*.class_id'   => 'required|exists:classes,id',
            'enrollment.*.existing'   => 'required|integer|min:0|max:99999',
        ]);

        $action = $request->input('action', 'save'); // 'save' or 'submit'

        DB::transaction(function () use ($request, $institution, $action) {
            foreach ($request->input('enrollment', []) as $item) {
                $ic = InstitutionClass::where('institution_id', $institution->id)
                    ->where('class_id', $item['class_id'])
                    ->where('enrollment_status', 'draft')
                    ->first();

                if (!$ic) continue;

                $ic->update([
                    'existing_enrollment' => (int) $item['existing'],
                    'enrollment_status'   => $action === 'submit' ? 'submitted' : 'draft',
                ]);
            }
        });

        $message = $action === 'submit'
            ? 'Enrollment submitted successfully.'
            : 'Enrollment saved as draft.';

        return redirect()->route('hoi.enrollment.index')
            ->with('success', $message);
    }
}

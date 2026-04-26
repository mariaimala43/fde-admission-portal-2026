<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\InstitutionClass;
use App\Models\DailyAdmission;
use App\Models\AcademicYear;

class AdmissionQuotaController extends Controller
{
    public function index()
    {
        $user        = Auth::user();
        $institution = $user->institution;

        if (! $institution) {
            return redirect()->route('hoi.profile.setup');
        }

        if (! $institution->classes_configured) {
            return redirect()->route('hoi.classes.setup')
                ->with('error', 'Please configure your classes first.');
        }

        $academicYear = AcademicYear::where('is_active', true)->first();

        $classes = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->with('classModel')
            ->orderBy('class_id')
            ->get();

        // Admissions submitted so far this year, per class
        $admitted = DailyAdmission::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->selectRaw('class_id,
                SUM(morning_boys + morning_girls + evening_boys + evening_girls
                  + oosc_boys + oosc_girls + p2p_boys + p2p_girls) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $totalQuota    = $classes->sum('admission_quota');
        $totalAdmitted = $admitted->sum();
        $totalAvailable = $classes->sum(function ($ic) use ($admitted) {
            if (! $ic->admission_quota) return 0;
            return max(0, $ic->admission_quota - ($admitted[$ic->class_id] ?? 0));
        });

        return view('hoi.quota.index', compact(
            'institution', 'classes', 'admitted',
            'totalQuota', 'totalAdmitted', 'totalAvailable', 'academicYear'
        ));
    }

    public function save(Request $request)
    {
        $user        = Auth::user();
        $institution = $user->institution;

        $request->validate([
            'quota'              => 'required|array',
            'quota.*.class_id'   => 'required|exists:classes,id',
            'quota.*.quota'      => 'nullable|integer|min:0|max:99999',
        ]);

        DB::transaction(function () use ($request, $institution) {
            foreach ($request->input('quota', []) as $item) {
                $quota = ($item['quota'] !== null && $item['quota'] !== '')
                    ? (int) $item['quota']
                    : null;

                InstitutionClass::where('institution_id', $institution->id)
                    ->where('class_id', (int) $item['class_id'])
                    ->where('is_active', true)
                    ->update(['admission_quota' => $quota]);
            }
        });

        return redirect()->route('hoi.quota.index')
            ->with('success', 'Admission quotas saved successfully.');
    }
}

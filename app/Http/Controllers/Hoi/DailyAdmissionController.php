<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\DailyAdmission;
use App\Models\InstitutionClass;
use App\Models\AcademicYear;

class DailyAdmissionController extends Controller
{
    public function index()
    {
        $user         = Auth::user();
        $institution  = $user->institution;

        if (!$institution) {
            return redirect()->route('hoi.profile.setup');
        }

        $today        = now()->toDateString();
        $academicYear = AcademicYear::where('is_active', true)->first();

        // All active classes for this institution
        $classes = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->with('classModel')
            ->orderBy('class_id')
            ->get();

        // Today's entries keyed by class_id
        $todayEntries = DailyAdmission::where('institution_id', $institution->id)
            ->where('admission_date', $today)
            ->get()
            ->keyBy('class_id');

        // Cumulative totals per class for this academic year
        $cumulative = DailyAdmission::where('institution_id', $institution->id)
            ->where('academic_year_id', $academicYear?->id)
            ->selectRaw('
                class_id,
                SUM(boys_count)                                                      as total_boys,
                SUM(girls_count)                                                     as total_girls,
                SUM(oosc_boys)                                                       as total_oosc_boys,
                SUM(oosc_girls)                                                      as total_oosc_girls,
                SUM(p2p_boys)                                                        as total_p2p_boys,
                SUM(p2p_girls)                                                       as total_p2p_girls,
                SUM(boys_count + girls_count + oosc_boys + oosc_girls + p2p_boys + p2p_girls) as total
            ')
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        $isPastCutoff = now()->gt(now()->endOfDay()->subSecond());

        return view('hoi.admissions.daily', compact(
            'institution', 'classes', 'todayEntries',
            'cumulative', 'today', 'academicYear', 'isPastCutoff'
        ));
    }

    public function save(Request $request)
    {
        $user         = Auth::user();
        $institution  = $user->institution;
        $today        = now()->toDateString();
        $academicYear = AcademicYear::where('is_active', true)->first();

        $request->validate([
            'admissions'               => 'required|array',
            'admissions.*.class_id'    => 'required|exists:classes,id',
            'admissions.*.boys_count'  => 'required|integer|min:0|max:9999',
            'admissions.*.girls_count' => 'required|integer|min:0|max:9999',
            'admissions.*.oosc_boys'   => 'required|integer|min:0|max:9999',
            'admissions.*.oosc_girls'  => 'required|integer|min:0|max:9999',
            'admissions.*.p2p_boys'    => 'required|integer|min:0|max:9999',
            'admissions.*.p2p_girls'   => 'required|integer|min:0|max:9999',
        ]);

        DB::transaction(function () use ($request, $institution, $today, $academicYear) {
            foreach ($request->input('admissions', []) as $item) {
                DailyAdmission::updateOrCreate(
                    [
                        'institution_id' => $institution->id,
                        'class_id'       => (int) $item['class_id'],
                        'admission_date' => $today,
                    ],
                    [
                        'academic_year_id' => $academicYear?->id,
                        'boys_count'       => (int) $item['boys_count'],
                        'girls_count'      => (int) $item['girls_count'],
                        'oosc_boys'        => (int) $item['oosc_boys'],
                        'oosc_girls'       => (int) $item['oosc_girls'],
                        'p2p_boys'         => (int) $item['p2p_boys'],
                        'p2p_girls'        => (int) $item['p2p_girls'],
                        'status'           => 'draft',
                        'submitted_by'     => Auth::id(),
                        'submitted_at'     => now(),
                    ]
                );
            }
        });

        return redirect()->route('hoi.admissions.daily')
            ->with('success', "Today's admissions saved for {$today}.");
    }
}

<?php

// SAVE AS: app/Http/Controllers/Fde/AdmissionPeriodController.php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\DailyAdmission;
use App\Models\Institution;
use Illuminate\Http\Request;

class AdmissionPeriodController extends Controller
{
    // ── Show current admission period settings ────────────────────────
    public function index()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $allYears   = AcademicYear::orderByDesc('id')->get();

        // Live stats for the active period
        $stats = null;
        if ($activeYear) {
            $stats = (object) [
                'total_institutions'  => Institution::where('is_active', true)->count(),
                'submitted_today'     => DailyAdmission::where('academic_year_id', $activeYear->id)
                                            ->whereDate('admission_date', today())
                                            ->distinct('institution_id')
                                            ->count('institution_id'),
                'days_elapsed'        => $activeYear->admission_start
                                            ? (int) now()->diffInDays($activeYear->admission_start, false) * -1
                                            : null,
                'days_remaining'      => $activeYear->admission_end
                                            ? (int) now()->startOfDay()->diffInDays($activeYear->admission_end->endOfDay(), false)
                                            : null,
                'is_open'             => $activeYear->isAdmissionOpen(),
                'is_cutoff_passed'    => $activeYear->isCutoffPassed(),
            ];
        }

        return view('fde.admission-period.index', compact('activeYear', 'allYears', 'stats'));
    }

    // ── Update admission period dates + cutoff ────────────────────────
    public function update(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'admission_start'   => 'required|date',
            'admission_end'     => 'required|date|after_or_equal:admission_start',
            'daily_cutoff_time' => 'required|date_format:H:i',
        ], [
            'admission_end.after_or_equal' => 'End date must be on or after start date.',
            'daily_cutoff_time.date_format' => 'Cutoff time must be in HH:MM format.',
        ]);

        $academicYear->update([
            'admission_start'   => $request->admission_start,
            'admission_end'     => $request->admission_end,
            'daily_cutoff_time' => $request->daily_cutoff_time . ':00', // store as H:i:s
        ]);

        return redirect()->route('fde.admission-period.index')
            ->with('success', "Admission period updated for {$academicYear->name}.");
    }
}

<?php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Institution;
use App\Models\DailyAdmission;
use App\Models\InstitutionClass;
use App\Models\Classes;
use App\Models\Sector;
use App\Models\AcademicYear;
use Carbon\Carbon;

class MasterReportController extends Controller
{
    public function index(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $sectors      = Sector::orderBy('name')->get();

        // ── Filters ────────────────────────────────────────
        $sectorId = $request->input('sector_id');
        $type     = $request->input('type');
        $gender   = $request->input('gender');
        $from     = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth();
        $to       = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        // ── Filtered institutions ─────────────────────────
        $institutions = Institution::with('sector')
        ->where('is_active', true)
        ->where('classes_configured', true)
        ->when($sectorId, fn($q) => $q->where('sector_id', $sectorId))
        ->when($type,     fn($q) => $q->where('type', $type))
        ->when($gender,   fn($q) => $q->where('gender', $gender))
        ->orderBy('sector_id')->orderBy('name')
        ->get();

        $institutionIds = $institutions->pluck('id');

        // ── All classes (ordered) ─────────────────────────
        $allClasses = Classes::orderBy('is_ece')->orderBy('order')->get();

        // ── Seat + enrollment per institution per class ───
        $seatData = InstitutionClass::whereIn('institution_id', $institutionIds)
            ->where('is_active', true)
            ->get()
            ->groupBy('institution_id');

        // ── Admission data per institution per class ──────
        $admissionData = DailyAdmission::whereIn('institution_id', $institutionIds)
            ->where('academic_year_id', $academicYear?->id)
            ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('
                institution_id,
                class_id,
                SUM(boys_count)                                                             as reg_boys,
                SUM(girls_count)                                                            as reg_girls,
                SUM(oosc_boys)                                                              as oosc_boys,
                SUM(oosc_girls)                                                             as oosc_girls,
                SUM(p2p_boys)                                                               as p2p_boys,
                SUM(p2p_girls)                                                              as p2p_girls,
                SUM(boys_count+girls_count+oosc_boys+oosc_girls+p2p_boys+p2p_girls)        as total_admitted
            ')
            ->groupBy('institution_id', 'class_id')
            ->get()
            ->groupBy('institution_id')
            ->map(fn($rows) => $rows->keyBy('class_id'));

        // ── Overall class summary (all schools combined) ──
        $overallByClass = [];
        foreach ($allClasses as $class) {
            $totalSeats    = 0;
            $totalExisting = 0;
            $totalRegular  = 0;
            $totalOosc     = 0;
            $totalP2p      = 0;
            $totalAdmitted = 0;
            $schoolCount   = 0;

            foreach ($institutions as $inst) {
                $seat = ($seatData[$inst->id] ?? collect())
                    ->firstWhere('class_id', $class->id);
                if (!$seat) continue;

                $schoolCount++;
                $adm = $admissionData[$inst->id][$class->id] ?? null;

                $totalSeats    += $seat->total_seats;
                $totalExisting += $seat->existing_enrollment;
                $totalRegular  += ($adm?->reg_boys  ?? 0) + ($adm?->reg_girls  ?? 0);
                $totalOosc     += ($adm?->oosc_boys ?? 0) + ($adm?->oosc_girls ?? 0);
                $totalP2p      += ($adm?->p2p_boys  ?? 0) + ($adm?->p2p_girls  ?? 0);
                $totalAdmitted += $adm?->total_admitted ?? 0;
            }

            if ($schoolCount === 0) continue;

            $totalFilled    = $totalExisting + $totalAdmitted;
            $totalRemaining = max(0, $totalSeats - $totalFilled);

            $overallByClass[$class->id] = [
                'class'          => $class,
                'school_count'   => $schoolCount,
                'total_seats'    => $totalSeats,
                'total_existing' => $totalExisting,
                'total_regular'  => $totalRegular,
                'total_oosc'     => $totalOosc,
                'total_p2p'      => $totalP2p,
                'total_admitted' => $totalAdmitted,
                'total_filled'   => $totalFilled,
                'total_remaining'=> $totalRemaining,
            ];
        }

        // ── Grand totals ──────────────────────────────────
        $grand = [
            'seats'     => collect($overallByClass)->sum('total_seats'),
            'existing'  => collect($overallByClass)->sum('total_existing'),
            'regular'   => collect($overallByClass)->sum('total_regular'),
            'oosc'      => collect($overallByClass)->sum('total_oosc'),
            'p2p'       => collect($overallByClass)->sum('total_p2p'),
            'admitted'  => collect($overallByClass)->sum('total_admitted'),
            'filled'    => collect($overallByClass)->sum('total_filled'),
            'remaining' => collect($overallByClass)->sum('total_remaining'),
        ];

        return view('fde.reports.master', compact(
            'institutions', 'allClasses', 'seatData', 'admissionData',
            'overallByClass', 'grand', 'sectors',
            'sectorId', 'type', 'gender', 'from', 'to',
            'academicYear'
        ));
    }
}

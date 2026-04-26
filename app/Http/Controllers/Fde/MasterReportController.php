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
use Illuminate\Support\Facades\Auth;

class MasterReportController extends Controller
{
    // ── Shared: resolve all filters & data from request ───────────────────────
    private function resolveData(Request $request): array
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $academicYear = AcademicYear::where('is_active', true)->first();
        $sectors      = Sector::orderBy('name')->get();

        $sectorId   = $request->input('sector_id');
        $type       = $request->input('type');
        $gender     = $request->input('gender');
        $classLevel = $request->input('class_level'); // 'all' | 'ece' | 'non_ece'
        $from       = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth();
        $to         = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        // ── Filtered institutions ─────────────────────────
        $institutions = Institution::with('sector')
            ->where('is_active', true)
            ->when($sectorId, fn($q) => $q->where('sector_id', $sectorId))
            ->when($type,     fn($q) => $q->where('type', $type))
            ->when($gender,   fn($q) => $q->where('gender', $gender))
            ->orderBy('sector_id')->orderBy('name')
            ->get();

        $institutionIds = $institutions->pluck('id');

        // ── All classes (ordered, optionally filtered by ECE) ────────
        $allClasses = Classes::orderBy('is_ece')->orderBy('order')
            ->when($classLevel === 'ece',     fn($q) => $q->where('is_ece', true))
            ->when($classLevel === 'non_ece', fn($q) => $q->where('is_ece', false))
            ->get();

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
                SUM(morning_boys)                                                           as morning_boys,
                SUM(morning_girls)                                                          as morning_girls,
                SUM(evening_boys)                                                           as evening_boys,
                SUM(evening_girls)                                                          as evening_girls,
                SUM(morning_boys+evening_boys)                                              as reg_boys,
                SUM(morning_girls+evening_girls)                                            as reg_girls,
                SUM(oosc_boys)                                                              as oosc_boys,
                SUM(oosc_girls)                                                             as oosc_girls,
                SUM(p2p_boys)                                                               as p2p_boys,
                SUM(p2p_girls)                                                              as p2p_girls,
                SUM(morning_boys+morning_girls)                                             as morning_total,
                SUM(evening_boys+evening_girls)                                             as evening_total,
                SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total_admitted
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
            $totalMorning  = 0;
            $totalEvening  = 0;
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
                $totalMorning  += $adm?->morning_total ?? 0;
                $totalEvening  += $adm?->evening_total ?? 0;
            }

            if ($schoolCount === 0) continue;

            $totalFilled    = $totalExisting + $totalAdmitted;
            $totalRemaining = max(0, $totalSeats - $totalFilled);

            $overallByClass[$class->id] = [
                'class'           => $class,
                'school_count'    => $schoolCount,
                'total_seats'     => $totalSeats,
                'total_existing'  => $totalExisting,
                'total_regular'   => $totalRegular,
                'total_oosc'      => $totalOosc,
                'total_p2p'       => $totalP2p,
                'total_admitted'  => $totalAdmitted,
                'total_morning'   => $totalMorning,
                'total_evening'   => $totalEvening,
                'total_filled'    => $totalFilled,
                'total_remaining' => $totalRemaining,
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

        return compact(
            'academicYear', 'sectors',
            'sectorId', 'type', 'gender', 'classLevel', 'from', 'to',
            'institutions', 'allClasses', 'seatData', 'admissionData',
            'overallByClass', 'grand'
        );
    }

    // ── index: render the master report view ──────────────────────────────────
    public function index(Request $request)
    {
        $data = $this->resolveData($request);

        $user = Auth::user();
        $exportPrefix = $user->hasRole('director') ? 'director' : 'fde';

        return view('fde.reports.master', array_merge($data, compact('exportPrefix')));
    }

    // ── export: stream CSV download with all filters applied ──────────────────
    public function export(Request $request)
    {
        $data = $this->resolveData($request);

        extract($data); // unpacks: $academicYear, $from, $to, $institutions,
                        //          $allClasses, $seatData, $admissionData,
                        //          $overallByClass, $grand

        $filename = 'master_report_' . $from->format('d-M-Y') . '_to_' . $to->format('d-M-Y') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use (
            $academicYear, $from, $to,
            $institutions, $allClasses,
            $seatData, $admissionData,
            $overallByClass, $grand
        ) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM — ensures Excel opens the file correctly
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // ── Report header ──────────────────────────────────────────────
            fputcsv($handle, ['Master Admission Report']);
            fputcsv($handle, [
                'Academic Year: ' . ($academicYear?->name ?? 'N/A'),
                'Period: ' . $from->format('d M Y') . ' to ' . $to->format('d M Y'),
                'Total Schools: ' . $institutions->count(),
            ]);
            fputcsv($handle, []);

            // ══════════════════════════════════════════════════════════════
            // SECTION 1 — OVERALL CLASS SUMMARY
            // ══════════════════════════════════════════════════════════════
            fputcsv($handle, ['SECTION 1 — OVERALL CLASS SUMMARY (ALL SCHOOLS COMBINED)']);
            fputcsv($handle, [
                'Class', 'ECE', 'Schools',
                'Total Seats', 'Promoted Students', 'Seats Available',
                'Regular Admitted', 'OOSC Admitted', 'P2G Admitted',
                'Morning', 'Evening',
                'Total Admitted', 'Total Filled', 'Fill Rate %',
            ]);

            foreach ($overallByClass as $row) {
                $fillRate = $row['total_seats'] > 0
                    ? round(($row['total_filled'] / $row['total_seats']) * 100)
                    : 0;

                fputcsv($handle, [
                    $row['class']->name,
                    $row['class']->is_ece ? 'Yes' : 'No',
                    $row['school_count'],
                    $row['total_seats'],
                    $row['total_existing'],
                    $row['total_remaining'],
                    $row['total_regular'],
                    $row['total_oosc'],
                    $row['total_p2p'],
                    $row['total_morning'],
                    $row['total_evening'],
                    $row['total_admitted'],
                    $row['total_filled'],
                    $fillRate . '%',
                ]);
            }

            // Grand total row
            $grandFillRate = $grand['seats'] > 0
                ? round(($grand['filled'] / $grand['seats']) * 100)
                : 0;

            fputcsv($handle, [
                'GRAND TOTAL', '', $institutions->count(),
                $grand['seats'], $grand['existing'], $grand['remaining'],
                $grand['regular'], $grand['oosc'], $grand['p2p'],
                '', '',
                $grand['admitted'], $grand['filled'], $grandFillRate . '%',
            ]);

            fputcsv($handle, []);

            // ══════════════════════════════════════════════════════════════
            // SECTION 2 — SCHOOL-WISE CLASS BREAKDOWN
            // ══════════════════════════════════════════════════════════════
            fputcsv($handle, ['SECTION 2 — SCHOOL-WISE CLASS BREAKDOWN']);
            fputcsv($handle, [
                'Sector', 'School', 'Type', 'Gender', 'Shift',
                'Class', 'ECE',
                'Total Seats', 'Promoted Students', 'Promoted Count', 'Repeaters',
                'Seats Available',
                'Reg Boys', 'Reg Girls',
                'OOSC Boys', 'OOSC Girls',
                'P2G Boys', 'P2G Girls',
                'Morning', 'Evening',
                'Total Admitted', 'Total Filled',
            ]);

            foreach ($institutions as $inst) {
                $instSeatData = $seatData[$inst->id] ?? collect();
                $instAdmData  = $admissionData[$inst->id] ?? collect();

                foreach ($instSeatData->sortBy('class_id') as $ic) {
                    $adm       = $instAdmData[$ic->class_id] ?? null;
                    $admitted  = $adm?->total_admitted ?? 0;
                    $filled    = $ic->existing_enrollment + $admitted;
                    $remaining = max(0, $ic->total_seats - $filled);

                    fputcsv($handle, [
                        $inst->sector?->name ?? '',
                        $inst->name,
                        $inst->type ?? '',
                        ucfirst(str_replace('_', ' ', $inst->gender ?? '')),
                        ucfirst($inst->shift ?? ''),
                        $ic->classModel?->name ?? '',
                        $ic->classModel?->is_ece ? 'Yes' : 'No',
                        $ic->total_seats,
                        $ic->existing_enrollment,
                        $ic->promoted_count ?? 0,
                        $ic->failed_count   ?? 0,
                        $remaining,
                        $adm?->reg_boys   ?? 0,
                        $adm?->reg_girls  ?? 0,
                        $adm?->oosc_boys  ?? 0,
                        $adm?->oosc_girls ?? 0,
                        $adm?->p2p_boys   ?? 0,
                        $adm?->p2p_girls  ?? 0,
                        $adm?->morning_total ?? 0,
                        $adm?->evening_total ?? 0,
                        $admitted,
                        $filled,
                    ]);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}

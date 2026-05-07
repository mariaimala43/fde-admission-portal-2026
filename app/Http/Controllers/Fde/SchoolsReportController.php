<?php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Institution;
use App\Models\DailyAdmission;
use App\Models\InstitutionClass;
use App\Models\Sector;
use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\Classes;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SchoolsReportController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    //  INDEX — list of all schools with admission + seat summary
    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $sectors      = Sector::orderBy('name')->get();
        $allClasses   = Classes::orderBy('id')->get();

        // ── Filters ────────────────────────────────────────────────────
        $search        = $request->input('search');
        $sectorId      = $request->input('sector_id');
        $type          = $request->input('type');
        $gender        = $request->input('gender');
        $classId       = $request->input('class_id');
        $hasTransport  = $request->boolean('has_transport');
        $hasMeal       = $request->boolean('has_meal_program');
        $hasMatricTech = $request->boolean('has_matric_tech');
        $isCambridge   = $request->boolean('is_cambridge');
        $hasEce        = $request->boolean('has_ece');

        // ── Schools query ──────────────────────────────────────────────
        $query = Institution::with('sector')
            ->where('is_active', true)
            ->when($search, fn($q) => $q->where(
                fn($s) => $s->where('name', 'like', "%{$search}%")
                             ->orWhere('code', 'like', "%{$search}%")
            ))
            ->when($sectorId === 'model_colleges',               fn($q) => $q->where('type', 'Model College'))
            ->when($sectorId && $sectorId !== 'model_colleges',  fn($q) => $q->where('sector_id', $sectorId))
            ->when($type,          fn($q) => $q->where('type', $type))
            ->when($gender,        fn($q) => $q->where('gender', $gender))
            ->when($classId,       fn($q) => $q->whereHas('institutionClasses',
                fn($ic) => $ic->where('class_id', $classId)->where('is_active', true)
            ))
            ->when($hasTransport,  fn($q) => $q->where('has_transport', true))
            ->when($hasMeal,       fn($q) => $q->where('has_meal_program', true))
            ->when($hasMatricTech, fn($q) => $q->where('has_matric_tech', true))
            ->when($isCambridge,   fn($q) => $q->where('is_cambridge', true))
            ->when($hasEce,        fn($q) => $q->where('has_ece', true))
            ->orderBy('sector_id')
            ->orderBy('name');

        $institutions = $query->paginate(20)->withQueryString();
        $pageIds      = $institutions->pluck('id');

        // ── Admission summary per school (active academic year) ────────
        $admissionSummary = DailyAdmission::when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->whereIn('institution_id', $pageIds)
            ->selectRaw('
                institution_id,
                SUM(
                    morning_boys + evening_boys + morning_girls + evening_girls +
                    morning_oosc_boys + morning_oosc_girls + evening_oosc_boys + evening_oosc_girls +
                    morning_p2p_boys  + morning_p2p_girls  + evening_p2p_boys  + evening_p2p_girls
                ) as total,
                SUM(morning_boys + evening_boys + morning_girls + evening_girls) as regular,
                SUM(morning_oosc_boys + morning_oosc_girls + evening_oosc_boys + evening_oosc_girls) as oosc,
                SUM(morning_p2p_boys  + morning_p2p_girls  + evening_p2p_boys  + evening_p2p_girls)  as p2p,
                SUM(matric_tech_count) as matric_tech
            ')
            ->groupBy('institution_id')
            ->get()
            ->keyBy('institution_id');

        // ── Seat summary per school (active classes only) ──────────────
        $seatSummary = InstitutionClass::whereIn('institution_id', $pageIds)
            ->where('is_active', true)
            ->selectRaw('institution_id, SUM(total_seats) as seats, SUM(existing_enrollment) as enrolled')
            ->groupBy('institution_id')
            ->get()
            ->keyBy('institution_id');

        // ── Matric Tech baseline per school (Class 9 & 10 existing) ───
        $matricTechBaseSummary = InstitutionClass::whereIn('institution_id', $pageIds)
            ->where('is_active', true)
            ->whereHas('classModel', fn($q) => $q->whereIn('order', [9, 10]))
            ->selectRaw('institution_id, SUM(matric_tech_existing) as matric_tech_base')
            ->groupBy('institution_id')
            ->get()
            ->keyBy('institution_id');

        return view('fde.schools.index', compact(
            'institutions', 'sectors', 'allClasses', 'admissionSummary',
            'seatSummary', 'matricTechBaseSummary', 'sectorId', 'type', 'gender',
            'classId', 'academicYear', 'search'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  SHOW — single school detailed report
    // ─────────────────────────────────────────────────────────────────
    public function show(Institution $institution, Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : ($academicYear?->admission_start && Carbon::parse($academicYear->admission_start)->lte(now())
                ? Carbon::parse($academicYear->admission_start)->startOfDay()
                : now()->startOfYear());

        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        $institution->load('sector');

        $hasEvening    = (bool) $institution->has_evening_classes;
        $hasMatricTech = (bool) $institution->has_matric_tech;

        // ── Day-by-day rows (raw model rows — use actual column names) ─
        $dailyRows = DailyAdmission::where('institution_id', $institution->id)
            ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
            ->with('classModel')
            ->orderBy('admission_date')
            ->orderBy('class_id')
            ->get();

        // ── Combined class summary (date-range, both shifts) ───────────
        // Aliases oosc_boys / p2p_boys etc. are safe here because this is
        // an aggregate query — NOT raw model rows.
        $classSummary = DailyAdmission::where('institution_id', $institution->id)
            ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('
                class_id,
                SUM(morning_boys  + evening_boys)   as reg_boys,
                SUM(morning_girls + evening_girls)  as reg_girls,
                SUM(morning_oosc_boys  + evening_oosc_boys)   as oosc_boys,
                SUM(morning_oosc_girls + evening_oosc_girls)  as oosc_girls,
                SUM(morning_p2p_boys   + evening_p2p_boys)    as p2p_boys,
                SUM(morning_p2p_girls  + evening_p2p_girls)   as p2p_girls,
                SUM(matric_tech_count)                        as matric_tech_count,
                SUM(
                    morning_boys  + evening_boys  + morning_girls + evening_girls +
                    morning_oosc_boys + evening_oosc_boys + morning_oosc_girls + evening_oosc_girls +
                    morning_p2p_boys  + evening_p2p_boys  + morning_p2p_girls  + evening_p2p_girls
                ) as total
            ')
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        // ── Per-shift summaries (only needed for dual-shift schools) ───
        $classSummaryMorning = collect();
        $classSummaryEvening = collect();

        if ($hasEvening) {
            // FIX: matric_tech_count added to both shift queries
            $classSummaryMorning = DailyAdmission::where('institution_id', $institution->id)
                ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('
                    class_id,
                    SUM(morning_boys)  as reg_boys,
                    SUM(morning_girls) as reg_girls,
                    SUM(morning_oosc_boys)  as oosc_boys,
                    SUM(morning_oosc_girls) as oosc_girls,
                    SUM(morning_p2p_boys)   as p2p_boys,
                    SUM(morning_p2p_girls)  as p2p_girls,
                    SUM(matric_tech_count)  as matric_tech_count,
                    SUM(
                        morning_boys  + morning_girls +
                        morning_oosc_boys  + morning_oosc_girls +
                        morning_p2p_boys   + morning_p2p_girls
                    ) as total
                ')
                ->groupBy('class_id')
                ->get()
                ->keyBy('class_id');

            $classSummaryEvening = DailyAdmission::where('institution_id', $institution->id)
                ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('
                    class_id,
                    SUM(evening_boys)  as reg_boys,
                    SUM(evening_girls) as reg_girls,
                    SUM(evening_oosc_boys)  as oosc_boys,
                    SUM(evening_oosc_girls) as oosc_girls,
                    SUM(evening_p2p_boys)   as p2p_boys,
                    SUM(evening_p2p_girls)  as p2p_girls,
                    SUM(matric_tech_count)  as matric_tech_count,
                    SUM(
                        evening_boys  + evening_girls +
                        evening_oosc_boys  + evening_oosc_girls +
                        evening_p2p_boys   + evening_p2p_girls
                    ) as total
                ')
                ->groupBy('class_id')
                ->get()
                ->keyBy('class_id');
        }

        // ── Active institution classes (for seat/enrollment data) ──────
        $classes = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->with('classModel')
            ->orderBy('class_id')
            ->get();

        // ── Section counts ─────────────────────────────────────────────
        $sectionCounts = DB::table('institution_sections')
            ->where('institution_id', $institution->id)
            ->where('is_active', true)
            ->select('class_id', DB::raw('COUNT(*) as count'))
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        // ── Full-year admitted totals (for available-seat calculation) ─
        // Always uses the full academic year regardless of the date filter,
        // so "Seats Available" stays accurate even when drilling into a
        // short date range.
        $yearlyAdmitted = DailyAdmission::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->selectRaw('
                class_id,
                SUM(
                    morning_boys  + morning_girls + evening_boys  + evening_girls +
                    morning_oosc_boys  + morning_oosc_girls + evening_oosc_boys  + evening_oosc_girls +
                    morning_p2p_boys   + morning_p2p_girls  + evening_p2p_boys   + evening_p2p_girls
                ) as total,
                SUM(
                    morning_boys  + morning_girls +
                    morning_oosc_boys  + morning_oosc_girls +
                    morning_p2p_boys   + morning_p2p_girls
                ) as morning_total,
                SUM(
                    evening_boys  + evening_girls +
                    evening_oosc_boys  + evening_oosc_girls +
                    evening_p2p_boys   + evening_p2p_girls
                ) as evening_total,
                SUM(matric_tech_count) as matric_tech_count
            ')
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        // ── Pre-compute per-class seat stats ───────────────────────────
        // All seat math lives here so the blade stays clean and the footer
        // total always equals the sum of the visible row values (clamped sums).
        //
        // Per-shift seat strategy:
        //   • Only use per-shift figures when the institution_classes table
        //     carries explicit morning_seats / evening_seats columns AND those
        //     columns actually have values.
        //   • If the columns are absent or both zero, we do NOT guess or split.
        //     We expose the combined available for both shift tabs so the user
        //     sees a truthful number instead of a fabricated 50/50 split.
        $classStats = $classes->map(function (InstitutionClass $ic) use ($yearlyAdmitted) {
            $yr        = $yearlyAdmitted[$ic->class_id] ?? null;
            $yrTotal   = (int) ($yr?->total         ?? 0);
            $yrMorning = (int) ($yr?->morning_total ?? 0);
            $yrEvening = (int) ($yr?->evening_total ?? 0);

            // Combined available — always correct regardless of shift data
            $available = max(0, $ic->total_seats - $ic->existing_enrollment - $yrTotal);

            $mSeatsRaw = $ic->morning_seats ?? null;
            $eSeatsRaw = $ic->evening_seats ?? null;
            $hasShiftColumns = $mSeatsRaw !== null && ($mSeatsRaw > 0 || (int)($eSeatsRaw ?? 0) > 0);

            if ($hasShiftColumns) {
                // Explicit per-shift capacity stored in DB — use it directly
                $mSeats = (int) $mSeatsRaw;
                $eSeats = (int) ($eSeatsRaw ?? 0);
                $mExist = (int) ($ic->morning_existing ?? 0);
                $eExist = (int) ($ic->evening_existing ?? 0);

                $availableMorning = max(0, $mSeats - $mExist - $yrMorning);
                $availableEvening = max(0, $eSeats - $eExist - $yrEvening);
                $totalMorning     = $mExist + $yrMorning;
                $totalEvening     = $eExist + $yrEvening;
            } else {
                // Per-shift seat capacity not configured (morning_seats = evening_seats = 0).
                // Matches HOI daily admission fallback exactly (DailyAdmissionController line ~161):
                //   morning gets all capacity, evening gets 0 seats.
                // This guarantees morning/evening tabs always show different values and is
                // consistent with what the HOI form shows during admission entry.
                $mSeats = (int) $ic->total_seats;
                $eSeats = 0;
                $mExist = (int) $ic->existing_enrollment;
                $eExist = 0;

                $availableMorning = max(0, $mSeats - $mExist - $yrMorning);
                $availableEvening = 0; // no evening seat capacity allocated
                $totalMorning     = $mExist + $yrMorning;
                $totalEvening     = $yrEvening; // evening admits only (no existing split available)
            }

            return [
                'class_id'         => $ic->class_id,
                'yrTotal'          => $yrTotal,
                'yrMorning'        => $yrMorning,
                'yrEvening'        => $yrEvening,
                'hasShiftColumns'  => $hasShiftColumns,
                'mSeats'           => $mSeats,
                'eSeats'           => $eSeats,
                'mExist'           => $mExist,
                'eExist'           => $eExist,
                'available'        => $available,
                'availableMorning' => $availableMorning,
                'availableEvening' => $availableEvening,
                // "Current enrollment" = existing (start-of-year) + newly admitted all year
                'totalEnrl'        => $ic->existing_enrollment + $yrTotal,
                'totalMorning'     => $totalMorning,
                'totalEvening'     => $totalEvening,
            ];
        })->keyBy('class_id');

        // ── Footer totals (computed as clamped sums, not raw aggregate) ─
        // FIX: summing clamped per-class values keeps the footer consistent
        // with what is displayed in each row.
        $footerStats = [
            'totalSeats'          => $classes->sum('total_seats'),
            'totalSeatsM'         => $classStats->sum('mSeats'),
            'totalSeatsE'         => $classStats->sum('eSeats'),
            'totalExisting'       => $classes->sum('existing_enrollment'),
            'totalExistingM'      => $classStats->sum('mExist'),
            'totalExistingE'      => $classStats->sum('eExist'),
            'totalAvailable'      => $classStats->sum('available'),
            'totalAvailableMorn'  => $classStats->sum('availableMorning'),
            'totalAvailableEven'  => $classStats->sum('availableEvening'),
            'totalEnrollment'     => $classStats->sum('totalEnrl'),
            'totalEnrollmentMorn' => $classStats->sum('totalMorning'),
            'totalEnrollmentEven' => $classStats->sum('totalEvening'),
        ];

        // ── Grand totals for the top summary cards (date-range) ────────
        $grandRegular    = $classSummary->sum(fn($r) => $r->reg_boys  + $r->reg_girls);
        $grandOosc       = $classSummary->sum(fn($r) => $r->oosc_boys + $r->oosc_girls);
        $grandP2p        = $classSummary->sum(fn($r) => $r->p2p_boys  + $r->p2p_girls);
        $grandTotal      = $classSummary->sum('total');
        // Full academic-year matric tech — matches HOI dashboard which also sums the full year.
        // $classSummary is date-range filtered so using it here would show fewer students
        // whenever the date filter doesn't cover the entire year.
        $grandMatricTech = (int) $yearlyAdmitted->sum('matric_tech_count');

        return view('fde.schools.show', compact(
            'institution', 'dailyRows', 'classSummary', 'classes',
            'grandRegular', 'grandOosc', 'grandP2p', 'grandTotal', 'grandMatricTech',
            'from', 'to', 'academicYear', 'sectionCounts',
            'hasEvening', 'classSummaryMorning', 'classSummaryEvening', 'hasMatricTech',
            'yearlyAdmitted', 'classStats', 'footerStats'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  QUOTA — set admission quota per class for a school (FDE only)
    // ─────────────────────────────────────────────────────────────────
    public function saveQuota(Institution $institution, Request $request)
    {
        $request->validate([
            'quota'            => 'required|array',
            'quota.*.class_id' => 'required|exists:classes,id',
            'quota.*.quota'    => 'nullable|integer|min:0|max:99999',
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

        AuditLog::record(
            action:        'quota_updated',
            modelType:     Institution::class,
            modelId:       $institution->id,
            oldValues:     [],
            newValues:     ['quotas' => $request->input('quota', [])],
            reason:        'FDE Cell updated admission quotas.',
            institutionId: $institution->id,
        );

        return redirect()->route('fde.schools.show', $institution)
            ->with('success', 'Admission quotas for ' . $institution->name . ' saved successfully.');
    }

    // ─────────────────────────────────────────────────────────────────
    //  RESET — wipe all daily_admissions for one school (active year)
    // ─────────────────────────────────────────────────────────────────
    public function resetAdmissions(Institution $institution, Request $request)
    {
        $request->validate([
            'confirmation' => ['required', 'in:RESET'],
        ], [
            'confirmation.in' => 'Type exactly: RESET to confirm.',
        ]);

        $academicYear = AcademicYear::where('is_active', true)->first();

        $deleted = DailyAdmission::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->count();

        DailyAdmission::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->delete();

        AuditLog::record(
            action:        'admission_data_reset',
            modelType:     Institution::class,
            modelId:       $institution->id,
            oldValues:     ['daily_admission_rows_deleted' => $deleted],
            newValues:     ['daily_admissions' => 'cleared'],
            reason:        $request->input('reason') ?: 'School submitted wrong data — reset by FDE Cell.',
            institutionId: $institution->id,
        );

        return redirect()->route('fde.schools.show', $institution)
            ->with('success', "Admission data for {$institution->name} has been reset. {$deleted} record(s) deleted. The school can now re-enter their data.");
    }
}

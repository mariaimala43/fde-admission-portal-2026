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
    public function index(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $sectors      = Sector::orderBy('name')->get();
        $allClasses   = Classes::orderBy('id')->get();

        // ── Filters ────────────────────────────────────────
        $search          = $request->input('search');
        $sectorId        = $request->input('sector_id');
        $type            = $request->input('type');
        $gender          = $request->input('gender');
        $classId         = $request->input('class_id');
        $hasTransport    = $request->boolean('has_transport');
        $hasMeal         = $request->boolean('has_meal_program');
        $hasMatricTech   = $request->boolean('has_matric_tech');
        $isCambridge     = $request->boolean('is_cambridge');
        $hasEce          = $request->boolean('has_ece');

        // ── Schools query ──────────────────────────────────
        $query = Institution::with('sector')
            ->where('is_active', true)
            ->when($search,        fn($q) => $q->where(fn($s) => $s->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->when($sectorId === 'model_colleges',              fn($q) => $q->where('type', 'Model College'))
            ->when($sectorId && $sectorId !== 'model_colleges', fn($q) => $q->where('sector_id', $sectorId))
            ->when($type,          fn($q) => $q->where('type', $type))
            ->when($gender,        fn($q) => $q->where('gender', $gender))
            ->when($classId,       fn($q) => $q->whereHas('institutionClasses', fn($ic) => $ic->where('class_id', $classId)->where('is_active', true)))
            ->when($hasTransport,  fn($q) => $q->where('has_transport', true))
            ->when($hasMeal,       fn($q) => $q->where('has_meal_program', true))
            ->when($hasMatricTech, fn($q) => $q->where('has_matric_tech', true))
            ->when($isCambridge,   fn($q) => $q->where('is_cambridge', true))
            ->when($hasEce,        fn($q) => $q->where('has_ece', true))
            ->orderBy('sector_id')
            ->orderBy('name');

        $institutions = $query->paginate(20)->withQueryString();

        $pageIds = $institutions->pluck('id');

        // ── Admission summary per school ───────────────────
        $admissionSummary = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->whereIn('institution_id', $pageIds)
            ->selectRaw('
                institution_id,
                SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total,
                SUM(morning_boys+evening_boys+morning_girls+evening_girls)   as regular,
                SUM(oosc_boys + oosc_girls)     as oosc,
                SUM(p2p_boys + p2p_girls)       as p2p
            ')
            ->groupBy('institution_id')
            ->get()
            ->keyBy('institution_id');

        // ── Seat summary per school ────────────────────────
        $seatSummary = InstitutionClass::whereIn('institution_id', $pageIds)
            ->selectRaw('institution_id, SUM(total_seats) as seats, SUM(existing_enrollment) as enrolled')
            ->groupBy('institution_id')
            ->get()
            ->keyBy('institution_id');

        return view('fde.schools.index', compact(
            'institutions', 'sectors', 'allClasses', 'admissionSummary',
            'seatSummary', 'sectorId', 'type', 'gender', 'classId', 'academicYear',
            'search'
        ));
    }

    public function show(Institution $institution, Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth();

        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        $institution->load('sector');

        // Day-by-day rows
        $dailyRows = DailyAdmission::where('institution_id', $institution->id)
            ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
            ->with('classModel')
            ->orderBy('admission_date')
            ->orderBy('class_id')
            ->get();

        // Class summary (combined)
        $classSummary = DailyAdmission::where('institution_id', $institution->id)
            ->whereBetween('admission_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('
                class_id,
                SUM(morning_boys+evening_boys)   as reg_boys,  SUM(morning_girls+evening_girls)  as reg_girls,
                SUM(morning_oosc_boys+evening_oosc_boys)   as oosc_boys,
                SUM(morning_oosc_girls+evening_oosc_girls) as oosc_girls,
                SUM(morning_p2p_boys+evening_p2p_boys)     as p2p_boys,
                SUM(morning_p2p_girls+evening_p2p_girls)   as p2p_girls,
                SUM(
                    morning_boys+evening_boys+morning_girls+evening_girls+
                    morning_oosc_boys+evening_oosc_boys+morning_oosc_girls+evening_oosc_girls+
                    morning_p2p_boys+evening_p2p_boys+morning_p2p_girls+evening_p2p_girls
                ) as total
            ')
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        // Per-shift summaries for evening schools
        $hasEvening = (bool) $institution->has_evening_classes;

        $classSummaryMorning = collect();
        $classSummaryEvening = collect();

        if ($hasEvening) {
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
                    SUM(
                        morning_boys+morning_girls+
                        morning_oosc_boys+morning_oosc_girls+
                        morning_p2p_boys+morning_p2p_girls
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
                    SUM(
                        evening_boys+evening_girls+
                        evening_oosc_boys+evening_oosc_girls+
                        evening_p2p_boys+evening_p2p_girls
                    ) as total
                ')
                ->groupBy('class_id')
                ->get()
                ->keyBy('class_id');
        }

        $classes = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->with('classModel')
            ->orderBy('class_id')
            ->get();

        $sectionCounts = DB::table('institution_sections')
            ->where('institution_id', $institution->id)
            ->where('is_active', true)
            ->select('class_id', DB::raw('COUNT(*) as count'))
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        $grandRegular = $classSummary->sum(fn($r) => $r->reg_boys + $r->reg_girls);
        $grandOosc    = $classSummary->sum(fn($r) => $r->oosc_boys + $r->oosc_girls);
        $grandP2p     = $classSummary->sum(fn($r) => $r->p2p_boys + $r->p2p_girls);
        $grandTotal   = $classSummary->sum('total');

        return view('fde.schools.show', compact(
            'institution', 'dailyRows', 'classSummary', 'classes',
            'grandRegular', 'grandOosc', 'grandP2p', 'grandTotal',
            'from', 'to', 'academicYear', 'sectionCounts',
            'hasEvening', 'classSummaryMorning', 'classSummaryEvening'
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

        // Count rows before deletion so we can report how many were removed
        $deleted = DailyAdmission::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->count();

        DailyAdmission::where('institution_id', $institution->id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->delete();

        // Audit trail
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

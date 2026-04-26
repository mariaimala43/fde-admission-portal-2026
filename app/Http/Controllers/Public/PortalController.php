<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Fde\PortalSettingsController;
use Illuminate\Http\Request;
use App\Models\Institution;
use App\Models\InstitutionClass;
use App\Models\InstitutionMeritList;
use App\Models\DailyAdmission;
use App\Models\Classes;
use App\Models\Sector;
use App\Models\AcademicYear;

class PortalController extends Controller
{
    public function index(Request $request)
    {
        $settings     = PortalSettingsController::get();
        $sectors      = Sector::orderBy('name')->get();
        $classes      = Classes::where('is_ece', false)->orderBy('order')->get();
        $academicYear = AcademicYear::where('is_active', true)->first();

        $totalInstitutions = Institution::where('is_active', true)->count();

        // Treat 'not_started' as open when academic year window is active
        $today      = now()->toDateString();
        $ayStart    = $academicYear?->admission_start
            ? (is_string($academicYear->admission_start) ? $academicYear->admission_start : $academicYear->admission_start->toDateString())
            : null;
        $ayEnd      = $academicYear?->admission_end
            ? (is_string($academicYear->admission_end) ? $academicYear->admission_end : $academicYear->admission_end->toDateString())
            : null;
        $windowOpen = $ayStart && $ayEnd && $today >= $ayStart && $today <= $ayEnd;
        $openStatuses = $windowOpen ? ['open', 'not_started'] : ['open'];

        $totalAdmittedThisYear = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->selectRaw('SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total')
            ->value('total') ?? 0;

        // ── Match Director dashboard formula: ALL active institution_classes ──
        // Director: totalAvailable = SUM(total_seats) - SUM(existing_enrollment) - totalAdmitted
        // (no admission_status restriction — same numbers shown to public and director)
        $grandSeats = InstitutionClass::where('is_active', true)
            ->selectRaw('SUM(total_seats) as ts, SUM(existing_enrollment) as ee')
            ->first();
        $totalSeatsAvailable = max(0, (int)($grandSeats->ts ?? 0) - (int)($grandSeats->ee ?? 0) - (int)$totalAdmittedThisYear);

        $query = Institution::with(['sector', 'institutionClasses.classModel'])
            ->where('is_active', true)
            ->where(function ($q) use ($openStatuses) {
                // Regular schools: must have seats configured and admission open/not_started (within window)
                // Model Colleges: always show regardless of admission_status / classes_configured
                $q->where(function ($inner) use ($openStatuses) {
                    $inner->where('classes_configured', true)
                          ->whereIn('admission_status', $openStatuses);
                })->orWhere('type', 'Model College');
            });

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhereHas('sector', fn($sq) => $sq->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('unionCouncil', fn($uq) => $uq->where('name', 'like', "%{$s}%"));
            });
        }
        if ($request->filled('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }
        if ($request->boolean('has_transport')) {
            $query->where('has_transport', true);
        }
        if ($request->boolean('has_meal_program')) {
            $query->where('has_meal_program', true);
        }
        if ($request->boolean('has_matric_tech')) {
            $query->where('has_matric_tech', true);
        }
        if ($request->boolean('has_evening_classes')) {
            $query->where('has_evening_classes', true);
        }
        if ($request->boolean('is_cambridge')) {
            $query->where('is_cambridge', true);
        }
        if ($request->boolean('has_ece')) {
            $query->where('has_ece', true);
        }

        $institutions = $query->orderBy('name')->get();

        // IDs of institutions that have at least one merit list file (for badge on cards)
        $institutionsWithMeritLists = InstitutionMeritList::whereIn('institution_id', $institutions->pluck('id'))
            ->distinct()->pluck('institution_id')->flip();

        $admissionTotals = DailyAdmission::where('academic_year_id', $academicYear?->id)
            ->whereIn('institution_id', $institutions->pluck('id'))
            ->selectRaw('institution_id, SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total_admitted')
            ->groupBy('institution_id')
            ->get()
            ->keyBy('institution_id');

        $seatData = InstitutionClass::whereIn('institution_id', $institutions->pluck('id'))
            ->where('is_active', true)
            ->with('classModel')
            ->get()
            ->groupBy('institution_id');

        // Hero stat: open schools that still have seats remaining.
        // Computed before class/vacancy filters so it's a stable global number,
        // not affected by what the user is currently searching for.
        $openInstitutions = $institutions->filter(function ($inst) use ($seatData, $admissionTotals) {
            $instSeats  = $seatData[$inst->id] ?? collect();
            $totalSeats = $instSeats->sum('total_seats');
            $existing   = $instSeats->sum('existing_enrollment');
            $admitted   = (int)($admissionTotals[$inst->id]?->total_admitted ?? 0);
            return ($totalSeats - $existing - $admitted) > 0;
        })->count();

        if ($request->filled('class_id')) {
            $classId = $request->class_id;
            $institutions = $institutions->filter(function ($inst) use ($seatData, $admissionTotals, $classId) {
                $classSeat = ($seatData[$inst->id] ?? collect())->firstWhere('class_id', $classId);
                if (!$classSeat) {
                    return false;
                }
                $admitted  = $admissionTotals[$inst->id]?->total_admitted ?? 0;
                $available = $classSeat->total_seats - $classSeat->existing_enrollment - $admitted;
                return $available > 0;
            });
        }

        if ($request->filled('vacancy')) {
            $institutions = $institutions->filter(function ($inst) use ($seatData, $admissionTotals, $request) {
                $instSeats   = $seatData[$inst->id] ?? collect();
                $totalSeats  = $instSeats->sum('total_seats');
                $existing    = $instSeats->sum('existing_enrollment');
                $admitted    = $admissionTotals[$inst->id]?->total_admitted ?? 0;
                $available   = $totalSeats - $existing - $admitted;
                $fillPct     = $totalSeats > 0 ? (($existing + $admitted) / $totalSeats) * 100 : 100;
                return match ($request->vacancy) {
                    'has_seats'   => $available > 0,
                    'nearly_full' => $fillPct >= 80 && $available > 0,
                    'full'        => $available <= 0,
                    default       => true,
                };
            });
        } else {
            // Default (no vacancy filter chosen): hide institutions with no seats left.
            // Only show those where available = total_seats - existing - admitted > 0.
            $institutions = $institutions->filter(function ($inst) use ($seatData, $admissionTotals) {
                $instSeats  = $seatData[$inst->id] ?? collect();
                $totalSeats = $instSeats->sum('total_seats');
                $existing   = $instSeats->sum('existing_enrollment');
                $admitted   = (int)($admissionTotals[$inst->id]?->total_admitted ?? 0);
                return ($totalSeats - $existing - $admitted) > 0;
            });
        }

        return view('portal.index', compact(
            'institutions',
            'sectors',
            'classes',
            'seatData',
            'admissionTotals',
            'academicYear',
            'totalInstitutions',
            'openInstitutions',
            'totalSeatsAvailable',
            'totalAdmittedThisYear',
            'settings',
            'institutionsWithMeritLists'
        ));
    }

    public function meritLists()
    {
        $settings = PortalSettingsController::get();

        $institutions = Institution::whereHas('meritLists')
            ->with(['meritLists' => fn($q) => $q->latest(), 'sector'])
            ->orderBy('name')
            ->get();

        return view('portal.merit_lists', compact('institutions', 'settings'));
    }

    public function seats(Request $request)
    {
        $settings     = PortalSettingsController::get();
        $academicYear = AcademicYear::where('is_active', true)->first();

        // Sector code groupings — stable, defined in SectorSeeder
        $urbanCodes = ['URBAN-I', 'URBAN-II'];
        $ruralCodes = ['B-K', 'TARNOL', 'SIHALA', 'NILORE'];
        $modelCodes = ['MODEL'];

        // Card totals — single aggregated query per group (matches Director dashboard formula)
        $urbanTotal = $this->groupAvailableSeats($urbanCodes, $academicYear?->id);
        $ruralTotal = $this->groupAvailableSeats($ruralCodes, $academicYear?->id);
        $modelTotal = $this->groupAvailableSeats($modelCodes, $academicYear?->id);

        // Per-sector breakdown for display inside each card
        $sectorSeats    = $this->availableSeatsBySector($academicYear?->id);
        $urbanSectors   = $sectorSeats->whereIn('code', $urbanCodes)->values();
        $ruralSectors   = $sectorSeats->whereIn('code', $ruralCodes)->values();

        $area    = $request->input('area'); // 'urban' | 'rural' | 'model' | null
        $schools = collect();

        if (in_array($area, ['urban', 'rural', 'model'])) {
            $codes = match($area) {
                'urban' => $urbanCodes,
                'rural' => $ruralCodes,
                'model' => $modelCodes,
            };
            $sectorIds = Sector::whereIn('code', $codes)->pluck('id');

            $rawInstitutions = Institution::with(['sector'])
                ->where('is_active', true)
                ->whereIn('sector_id', $sectorIds)
                ->orderBy('name')
                ->get();

            $institutionIds = $rawInstitutions->pluck('id');

            $seatTotals = InstitutionClass::whereIn('institution_id', $institutionIds)
                ->where('is_active', true)
                ->selectRaw('institution_id, SUM(total_seats) as total_seats, SUM(existing_enrollment) as existing_enrollment')
                ->groupBy('institution_id')
                ->get()
                ->keyBy('institution_id');

            $admissionTotals = DailyAdmission::where('academic_year_id', $academicYear?->id)
                ->whereIn('institution_id', $institutionIds)
                ->selectRaw('institution_id, SUM(morning_boys + morning_girls + evening_boys + evening_girls) as filled')
                ->groupBy('institution_id')
                ->get()
                ->keyBy('institution_id');

            $schools = $rawInstitutions->map(function ($inst) use ($seatTotals, $admissionTotals) {
                $seats      = $seatTotals[$inst->id]     ?? null;
                $admissions = $admissionTotals[$inst->id] ?? null;

                $totalSeats         = (int) ($seats?->total_seats ?? 0);
                $existingEnrollment = (int) ($seats?->existing_enrollment ?? 0);
                $filledByAdmissions = (int) ($admissions?->filled ?? 0);
                $filledSeats        = $existingEnrollment + $filledByAdmissions;
                $availableSeats     = max(0, $totalSeats - $filledSeats);

                $inst->computed_total_seats     = $totalSeats;
                $inst->computed_filled_seats    = $filledSeats;
                $inst->computed_available_seats = $availableSeats;

                return $inst;
            })->filter(fn($inst) => $inst->computed_available_seats > 0)->values();
        }

        return view('portal.seats', compact(
            'settings', 'academicYear',
            'urbanTotal', 'ruralTotal', 'modelTotal',
            'urbanSectors', 'ruralSectors',
            'area', 'schools'
        ));
    }

    private function groupAvailableSeats(array $codes, ?int $academicYearId): int
    {
        $sectorIds      = Sector::whereIn('code', $codes)->pluck('id');
        $institutionIds = Institution::where('is_active', true)
            ->whereIn('sector_id', $sectorIds)
            ->pluck('id');

        if ($institutionIds->isEmpty()) {
            return 0;
        }

        $totals = InstitutionClass::whereIn('institution_id', $institutionIds)
            ->where('is_active', true)
            ->selectRaw('SUM(total_seats) as ts, SUM(existing_enrollment) as ee')
            ->first();

        // Match Director dashboard: subtract ALL admitted (regular + OOSC + P2P)
        $admitted = DailyAdmission::whereIn('institution_id', $institutionIds)
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->selectRaw('SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total')
            ->value('total') ?? 0;

        return max(0, (int) ($totals?->ts ?? 0) - (int) ($totals?->ee ?? 0) - (int) $admitted);
    }

    private function availableSeatsBySector(?int $academicYearId): \Illuminate\Support\Collection
    {
        $sectors = Sector::where('is_active', true)->get();

        return $sectors->map(function ($sector) use ($academicYearId) {
            $institutionIds = Institution::where('is_active', true)
                ->where('sector_id', $sector->id)
                ->pluck('id');

            $totals = InstitutionClass::whereIn('institution_id', $institutionIds)
                ->where('is_active', true)
                ->selectRaw('SUM(total_seats) as ts, SUM(existing_enrollment) as ee')
                ->first();

            // Match Director dashboard: subtract ALL admitted (regular + OOSC + P2P)
            $admitted = DailyAdmission::whereIn('institution_id', $institutionIds)
                ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
                ->selectRaw('SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total')
                ->value('total') ?? 0;

            $sector->available   = max(0, (int) ($totals?->ts ?? 0) - (int) ($totals?->ee ?? 0) - (int) $admitted);
            $sector->total_seats = (int) ($totals?->ts ?? 0);
            $sector->filled      = (int) ($totals?->ee ?? 0) + (int) $admitted;

            return $sector;
        });
    }

    public function show(Institution $institution)
    {
        $settings     = PortalSettingsController::get();
        $academicYear = AcademicYear::where('is_active', true)->first();

        $institution->load(['sector']);

        $meritLists = InstitutionMeritList::where('institution_id', $institution->id)
            ->latest()
            ->get();

        $seatData = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->with('classModel')
            ->orderBy('class_id')
            ->get();

        $admissionTotal = DailyAdmission::where('institution_id', $institution->id)
            ->where('academic_year_id', $academicYear?->id)
            ->selectRaw('class_id, SUM(morning_boys+evening_boys+morning_girls+evening_girls+oosc_boys+oosc_girls+p2p_boys+p2p_girls) as total_admitted')
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        return view('portal.show', compact(
            'institution',
            'seatData',
            'admissionTotal',
            'academicYear',
            'settings',
            'meritLists'
        ));
    }
}

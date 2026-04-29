<?php

namespace App\Http\Controllers\Aeo;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Institution;
use App\Models\InstitutionClass;
use App\Models\InstitutionSection;
use App\Models\DailyAdmission;
use App\Models\NewConstructionRoom;
use App\Models\AcademicYear;
use App\Models\Sector;

/**
 * AEO Dashboard — scoped to the AEO's assigned sector(s) via aeo_sectors pivot.
 * Directors have their own DashboardController under Director namespace.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $user         = Auth::user();
        $academicYear = AcademicYear::where('is_active', true)->first();

        // ── AEO sector scope — support multi-sector AEOs ──────────────
        $assignedSectors = $user->sectors()->orderBy('name')->get();

        if ($assignedSectors->isEmpty()) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'No sector assigned to your account. Please contact FDE Cell.');
        }

        $currentSector = $assignedSectors->first(); // used for blade backwards-compat
        $sectors       = $assignedSectors;
        $sectorIds     = $assignedSectors->pluck('id');

        $institutions = Institution::whereIn('sector_id', $sectorIds)
            ->where('is_active', true)
            ->with('sector')
            ->orderBy('sector_id')
            ->orderBy('name')
            ->get();

        $institutionIds = $institutions->pluck('id');

        // ── Seat data per institution (class-level) ───────────────────
        $seatData = InstitutionClass::whereIn('institution_id', $institutionIds)
            ->where('is_active', true)
            ->with('classModel')
            ->get()
            ->groupBy('institution_id');

        // ── Section counts per institution per class ──────────────────
        $sectionCounts = InstitutionSection::whereIn('institution_id', $institutionIds)
            ->selectRaw('institution_id, class_id, COUNT(*) as section_count')
            ->groupBy('institution_id', 'class_id')
            ->get()
            ->groupBy('institution_id')
            ->map(fn($rows) => $rows->keyBy('class_id'));

        // ── Cumulative admissions per institution per class ───────────
        $admissionData = DailyAdmission::whereIn('institution_id', $institutionIds)
            ->where('academic_year_id', $academicYear?->id)
            ->selectRaw('
                institution_id,
                class_id,
                SUM(
                    morning_boys  + evening_boys  +
                    morning_girls + evening_girls +
                    oosc_boys     + oosc_girls    +
                    p2p_boys      + p2p_girls
                ) as total_admitted
            ')
            ->groupBy('institution_id', 'class_id')
            ->get()
            ->groupBy('institution_id')
            ->map(fn($rows) => $rows->keyBy('class_id'));

        // ── Matric Tech totals (scoped to visible institutions) ───────
        $matricTechExisting = (int) InstitutionClass::whereIn('institution_id', $institutionIds)
            ->where('is_active', true)
            ->whereHas('institution', fn($q) => $q->where('has_matric_tech', true))
            ->whereHas('classModel',  fn($q) => $q->whereIn('order', [9, 10]))
            ->sum('matric_tech_existing');

        $matricTechYear = (int) DailyAdmission::whereIn('institution_id', $institutionIds)
            ->where('academic_year_id', $academicYear?->id)
            ->sum('matric_tech_count');

        $matricTechToday = (int) DailyAdmission::whereIn('institution_id', $institutionIds)
            ->whereDate('admission_date', today())
            ->sum('matric_tech_count');

        // ── New Construction Rooms (scoped to visible institutions) ───
        $roomsRow = NewConstructionRoom::whereIn('institution_id', $institutionIds)
            ->selectRaw('SUM(rooms_total) as total, SUM(rooms_allocated) as allocated')
            ->first();

        $newRoomsTotal       = (int) ($roomsRow->total     ?? 0);
        $newRoomsAllocated   = (int) ($roomsRow->allocated ?? 0);
        $newRoomsRemaining   = max(0, $newRoomsTotal - $newRoomsAllocated);
        $schoolsWithNewRooms = NewConstructionRoom::whereIn('institution_id', $institutionIds)
            ->distinct('institution_id')
            ->count('institution_id');

        // ── Sector-level summary ──────────────────────────────────────
        $sectorSummary = $sectors->map(function ($sector) use (
            $institutions, $seatData, $admissionData, $academicYear
        ) {
            $sectorInsts   = $institutions->where('sector_id', $sector->id);
            $sectorInstIds = $sectorInsts->pluck('id');

            $totalSeats    = 0;
            $totalExisting = 0;
            $totalAdmitted = 0;

            foreach ($sectorInstIds as $instId) {
                $classes    = $seatData[$instId]     ?? collect();
                $admissions = $admissionData[$instId] ?? collect();

                $totalSeats    += $classes->sum('total_seats');
                $totalExisting += $classes->sum('existing_enrollment');
                $totalAdmitted += $admissions->sum('total_admitted');
            }

            // Matric Tech for this sector
            $sectorMatricTech = (int) DailyAdmission::whereIn('institution_id', $sectorInstIds)
                ->where('academic_year_id', $academicYear?->id)
                ->sum('matric_tech_count');

            // New rooms for this sector
            $sectorRoomsRow = NewConstructionRoom::whereIn('institution_id', $sectorInstIds)
                ->selectRaw('SUM(rooms_total) as total, SUM(rooms_allocated) as allocated')
                ->first();

            $sectorNewRoomsTotal     = (int) ($sectorRoomsRow->total     ?? 0);
            $sectorNewRoomsAllocated = (int) ($sectorRoomsRow->allocated ?? 0);
            $sectorNewRoomsRemaining = max(0, $sectorNewRoomsTotal - $sectorNewRoomsAllocated);

            $sector->school_count        = $sectorInsts->count();
            $sector->total_seats         = $totalSeats;
            $sector->total_existing      = $totalExisting;
            $sector->total_admitted      = $totalAdmitted;
            $sector->total_available     = max(0, $totalSeats - $totalExisting - $totalAdmitted);
            $sector->total_enrollment    = $totalExisting + $totalAdmitted;
            $sector->matric_tech         = $sectorMatricTech;
            $sector->new_rooms_total     = $sectorNewRoomsTotal;
            $sector->new_rooms_allocated = $sectorNewRoomsAllocated;
            $sector->new_rooms_remaining = $sectorNewRoomsRemaining;

            return $sector;
        });

        // ── Grand totals (across all visible sectors) ─────────────────
        $grand = [
            'schools'    => $institutions->count(),
            'seats'      => $sectorSummary->sum('total_seats'),
            'existing'   => $sectorSummary->sum('total_existing'),
            'admitted'   => $sectorSummary->sum('total_admitted'),
            'available'  => $sectorSummary->sum('total_available'),
            'enrollment' => $sectorSummary->sum('total_enrollment'),
        ];

        return view('aeo.dashboard', compact(
            'sectors',
            'currentSector',
            'institutions',
            'seatData',
            'sectionCounts',
            'admissionData',
            'sectorSummary',
            'grand',
            'academicYear',
            'matricTechExisting',
            'matricTechToday',
            'matricTechYear',
            'newRoomsTotal',
            'newRoomsAllocated',
            'newRoomsRemaining',
            'schoolsWithNewRooms'
        ));
    }
}

<?php

// SAVE AS: app/Http/Controllers/Fde/RoomAllocationController.php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use App\Models\NewConstructionRoom;
use App\Models\RoomAllocation;
use App\Models\InstitutionClass;
use App\Models\DailyAdmission;
use App\Models\AcademicYear;
use App\Models\Sector;
use Illuminate\Http\Request;

class RoomAllocationController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    //  INDEX — system-wide list of all 56 schools with new rooms
    //  FDE can VIEW only — all allocation/enrollment done by HOI
    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        $query = NewConstructionRoom::with([
            'institution.sector',
            'allocations.classModel',
        ]);

        // ── Filters ───────────────────────────────────────────────────
        if ($request->filled('sector_id')) {
            $query->whereHas('institution', fn($q) =>
                $q->where('sector_id', $request->sector_id)
            );
        }
        if ($request->filled('construction_status')) {
            $query->where('construction_status', $request->construction_status);
        }
        if ($request->filled('allocation_status')) {
            match ($request->allocation_status) {
                'allocated'   => $query->where('rooms_allocated', '>', 0),
                'unallocated' => $query->where('rooms_allocated', 0),
                'full'        => $query->whereColumn('rooms_allocated', '>=', 'rooms_total'),
                default       => null,
            };
        }

        $records = $query
            ->join('institutions', 'new_construction_rooms.institution_id', '=', 'institutions.id')
            ->orderBy('new_construction_rooms.construction_status')
            ->orderBy('institutions.name')
            ->select('new_construction_rooms.*')
            ->paginate(50)
            ->withQueryString();

        // ── Pull enrollment for institutions on this page ─────────────
        $institutionIds = $records->pluck('institution_id');

        // All daily admissions per institution per class (all statuses)
        $admissionData = DailyAdmission::whereIn('institution_id', $institutionIds)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->selectRaw('
                institution_id,
                class_id,
                SUM(morning_boys  + evening_boys)  as boys,
                SUM(morning_girls + evening_girls) as girls,
                SUM(morning_oosc_boys  + evening_oosc_boys  + morning_p2p_boys  + evening_p2p_boys)  as oosc_p2p_boys,
                SUM(morning_oosc_girls + evening_oosc_girls + morning_p2p_girls + evening_p2p_girls) as oosc_p2p_girls
            ')
            ->groupBy('institution_id', 'class_id')
            ->get()
            ->groupBy('institution_id')
            ->map(fn($rows) => $rows->keyBy('class_id'));

        // Seat config (total_seats, existing_enrollment) per institution per class
        $seatData = InstitutionClass::whereIn('institution_id', $institutionIds)
            ->where('is_active', true)
            ->get()
            ->groupBy('institution_id')
            ->map(fn($rows) => $rows->keyBy('class_id'));

        // ── Attach enrollment stats to each record ────────────────────
        foreach ($records as $room) {
            $iid        = $room->institution_id;
            $admissions = $admissionData[$iid] ?? collect();
            $seats      = $seatData[$iid]      ?? collect();

            $room->enrollment_stats = $room->allocations->map(function ($alloc) use ($admissions, $seats) {
                $adm = $admissions[$alloc->class_id] ?? null;
                $ic  = $seats[$alloc->class_id]      ?? null;

                $boys  = ($adm?->boys          ?? 0) + ($adm?->oosc_p2p_boys  ?? 0);
                $girls = ($adm?->girls         ?? 0) + ($adm?->oosc_p2p_girls ?? 0);
                $newly = $boys + $girls;

                $existing   = $ic?->existing_enrollment ?? 0;
                $totalSeats = $ic?->total_seats          ?? ($alloc->rooms_assigned * 40);
                $totalEnrl  = $existing + $newly;
                $available  = max(0, $totalSeats - $totalEnrl);

                return [
                    'class_name'   => $alloc->classModel?->name ?? '—',
                    'rooms'        => $alloc->rooms_assigned,
                    'seats'        => $totalSeats,
                    'existing'     => $existing,
                    'boys'         => $boys,
                    'girls'        => $girls,
                    'newly'        => $newly,
                    'total_enroll' => $totalEnrl,
                    'available'    => $available,
                    'is_full'      => $totalEnrl >= $totalSeats,
                    'fill_pct'     => $totalSeats > 0 ? min(100, round(($totalEnrl / $totalSeats) * 100)) : 0,
                ];
            });
        }

        $sectors = Sector::orderBy('name')->get(['id', 'name']);

        // ── System-wide summary stats ─────────────────────────────────
        $stats = (object) [
            'total_schools'   => NewConstructionRoom::count(),
            'total_rooms'     => NewConstructionRoom::sum('rooms_total'),
            'allocated_rooms' => NewConstructionRoom::sum('rooms_allocated'),
            'completed'       => NewConstructionRoom::where('construction_status', 'completed')->count(),
            'near_completion' => NewConstructionRoom::where('construction_status', 'near_completion')->count(),
            'total_seats'     => NewConstructionRoom::sum('rooms_total') * 40,
        ];

        return view('fde.rooms.index', compact('records', 'sectors', 'stats', 'academicYear'));
    }

    // ─────────────────────────────────────────────────────────────────
    //  SHOW — per-school detail (read-only)
    // ─────────────────────────────────────────────────────────────────
    public function show(NewConstructionRoom $room)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        $room->load([
            'institution.sector',
            'allocations.classModel',
        ]);

        // Enrollment per class for this school
        $admissions = DailyAdmission::where('institution_id', $room->institution_id)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->selectRaw('
                class_id,
                SUM(morning_boys  + evening_boys)  as boys,
                SUM(morning_girls + evening_girls) as girls,
                SUM(morning_oosc_boys  + evening_oosc_boys  + morning_p2p_boys  + evening_p2p_boys)  as oosc_p2p_boys,
                SUM(morning_oosc_girls + evening_oosc_girls + morning_p2p_girls + evening_p2p_girls) as oosc_p2p_girls
            ')
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        // Seat config per class
        $seats = InstitutionClass::where('institution_id', $room->institution_id)
            ->where('is_active', true)
            ->get()
            ->keyBy('class_id');

        // Build enriched allocation rows
        $allocations = $room->allocations->map(function ($alloc) use ($admissions, $seats) {
            $adm = $admissions[$alloc->class_id] ?? null;
            $ic  = $seats[$alloc->class_id]      ?? null;

            $regBoys   = (int) ($adm?->boys          ?? 0);
            $regGirls  = (int) ($adm?->girls         ?? 0);
            $ooscBoys  = (int) ($adm?->oosc_p2p_boys  ?? 0);
            $ooscGirls = (int) ($adm?->oosc_p2p_girls ?? 0);
            $totalBoys  = $regBoys  + $ooscBoys;
            $totalGirls = $regGirls + $ooscGirls;
            $newly      = $totalBoys + $totalGirls;

            $existing   = $ic?->existing_enrollment ?? 0;
            $totalSeats = $ic?->total_seats          ?? ($alloc->rooms_assigned * 40);
            $totalEnrl  = $existing + $newly;
            $available  = $totalSeats - $totalEnrl;   // may be negative — show as-is for FDE

            $alloc->enroll = (object) [
                'existing'    => $existing,
                'reg_boys'    => $regBoys,
                'reg_girls'   => $regGirls,
                'oosc_boys'   => $ooscBoys,
                'oosc_girls'  => $ooscGirls,
                'total_boys'  => $totalBoys,
                'total_girls' => $totalGirls,
                'newly'       => $newly,
                'total_enroll'=> $totalEnrl,
                'total_seats' => $totalSeats,
                'available'   => $available,
                'fill_pct'    => $totalSeats > 0 ? min(100, round(($totalEnrl / $totalSeats) * 100)) : 0,
                'is_over'     => $totalEnrl > $totalSeats,
            ];

            return $alloc;
        })->sortBy(fn($a) => $a->classModel?->order ?? 99);

        // School-level totals
        $schoolTotals = (object) [
            'rooms'       => $room->rooms_total,
            'allocated'   => $room->rooms_allocated,
            'unallocated' => $room->roomsRemaining(),
            'seats_added' => $allocations->sum(fn($a) => $a->enroll->total_seats),
            'existing'    => $allocations->sum(fn($a) => $a->enroll->existing),
            'total_boys'  => $allocations->sum(fn($a) => $a->enroll->total_boys),
            'total_girls' => $allocations->sum(fn($a) => $a->enroll->total_girls),
            'newly'       => $allocations->sum(fn($a) => $a->enroll->newly),
            'total_enroll'=> $allocations->sum(fn($a) => $a->enroll->total_enroll),
            'available'   => $allocations->sum(fn($a) => $a->enroll->available),
        ];

        return view('fde.rooms.show', compact(
            'room', 'allocations', 'schoolTotals', 'academicYear'
        ));
    }
}

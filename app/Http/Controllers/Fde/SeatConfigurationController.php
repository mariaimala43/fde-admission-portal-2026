<?php

// SAVE AS: app/Http/Controllers/Fde/SeatConfigurationController.php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\InstitutionClass;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SeatConfigurationController extends Controller
{
    // ── Index — all institutions seat summary ─────────────────────────
    public function index(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        $query = Institution::where('is_active', true)
            ->with([
                'classes'         => fn($q) => $q->where('is_active', true)->with('classModel'),
                'seatsLockedBy',
            ])
            ->withCount(['classes as configured_classes_count' => fn($q) => $q->where('is_active', true)])
            ->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('locked')) {
            $request->locked === 'yes'
                ? $query->whereNotNull('seats_locked_at')
                : $query->whereNull('seats_locked_at');
        }

        $institutions = $query->paginate(30)->withQueryString();

        // Summary stats
        $totalSeats     = InstitutionClass::where('is_active', true)->sum('total_seats');
        $lockedCount    = Institution::whereNotNull('seats_locked_at')->count();
        $unlockedCount  = Institution::where('is_active', true)->whereNull('seats_locked_at')->count();

        return view('fde.seats.index', compact(
            'institutions', 'academicYear',
            'totalSeats', 'lockedCount', 'unlockedCount'
        ));
    }

    // ── Edit — per-institution seat form ──────────────────────────────
    public function edit(Institution $institution)
    {
        $institution->load(['seatsLockedBy']);

        $classes = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->with('classModel')
            ->orderBy('class_id')
            ->get();

        $academicYear = AcademicYear::where('is_active', true)->first();

        // Attach live vacancy to each class
        $classes->each(function ($ic) use ($academicYear) {
            $ic->available = $ic->availableSeats($academicYear?->id);
        });

        return view('fde.seats.edit', compact('institution', 'classes', 'academicYear'));
    }

    // ── Update — save total_seats per class ───────────────────────────
    public function update(Request $request, Institution $institution)
    {
        abort_if($institution->seats_locked_at !== null, 403,
            'Seat configuration is locked. Unlock it first.');

        $request->validate([
            'seats'          => 'required|array|min:1',
            'seats.*.class_id'     => 'required|exists:classes,id',
            'seats.*.total_seats'  => 'required|integer|min:0|max:9999',
        ]);

        DB::transaction(function () use ($request, $institution) {
            foreach ($request->seats as $row) {
                InstitutionClass::where('institution_id', $institution->id)
                    ->where('class_id', $row['class_id'])
                    ->update(['total_seats' => (int) $row['total_seats']]);
            }
        });

        return redirect()->route('fde.seats.edit', $institution)
            ->with('success', "Seat configuration updated for {$institution->name}.");
    }

    // ── Lock ──────────────────────────────────────────────────────────
    public function lock(Institution $institution)
    {
        abort_if($institution->seats_locked_at !== null, 422, 'Already locked.');
        abort_if(
            InstitutionClass::where('institution_id', $institution->id)
                ->where('is_active', true)->doesntExist(),
            422,
            'No classes configured. Cannot lock empty seat configuration.'
        );

        $institution->update([
            'seats_locked_by' => Auth::id(),
            'seats_locked_at' => now(),
        ]);

        return redirect()->route('fde.seats.edit', $institution)
            ->with('success', "Seat configuration locked for {$institution->name}. HOI can no longer change class setup.");
    }

    // ── Unlock ────────────────────────────────────────────────────────
    public function unlock(Request $request, Institution $institution)
    {
        abort_if($institution->seats_locked_at === null, 422, 'Not locked.');

        $request->validate([
            'unlock_reason' => 'required|string|min:10|max:500',
        ]);

        $institution->update([
            'seats_locked_by' => null,
            'seats_locked_at' => null,
        ]);

        return redirect()->route('fde.seats.edit', $institution)
            ->with('success', "Seat configuration unlocked. HOI can now edit class setup.");
    }
}

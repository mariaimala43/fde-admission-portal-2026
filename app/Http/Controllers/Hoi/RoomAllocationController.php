<?php

// SAVE AS: app/Http/Controllers/Hoi/RoomAllocationController.php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use App\Models\NewConstructionRoom;
use App\Models\RoomAllocation;
use App\Models\Classes;
use App\Models\InstitutionClass;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoomAllocationController extends Controller
{
    // ── Resolve the new construction record for this HOI's school ─────
    private function constructionRecord(): ?NewConstructionRoom
    {
        $institution = Auth::user()->institution;
        if (! $institution) return null;

        return NewConstructionRoom::with(['allocations.classModel'])
            ->where('institution_id', $institution->id)
            ->first();
    }

    // ── Index — show new rooms + existing allocations ─────────────────
    public function index()
    {
        $institution = Auth::user()->institution;
        abort_if(! $institution, 403, 'No institution assigned.');

        $construction = $this->constructionRecord();

        // If no new rooms recorded for this school, show info page
        if (! $construction) {
            return view('hoi.rooms.index', [
                'institution'  => $institution,
                'construction' => null,
                'allocations'  => collect(),
                'classes'      => collect(),
                'allocated'    => collect(),
            ]);
        }

        // Classes already configured at this school (only show those)
        $configuredClassIds = InstitutionClass::where('institution_id', $institution->id)
            ->where('is_active', true)
            ->pluck('class_id');

        $classes = Classes::whereIn('id', $configuredClassIds)
            ->orderBy('is_ece')
            ->orderBy('order')
            ->get();

        // Already allocated class IDs (for excluding from dropdown)
        $allocated = $construction->allocations->keyBy('class_id');

        return view('hoi.rooms.index', compact(
            'institution', 'construction', 'classes', 'allocated'
        ));
    }

    // ── Store a new room allocation ───────────────────────────────────
    public function store(Request $request)
    {
        $institution = Auth::user()->institution;
        abort_if(! $institution, 403);

        $construction = NewConstructionRoom::where('institution_id', $institution->id)->first();
        abort_if(! $construction, 404, 'No new construction rooms recorded for your school.');

        $request->validate([
            'class_id'      => 'required|integer|exists:classes,id',
            'rooms_assigned' => [
                'required', 'integer', 'min:1',
                'max:' . $construction->roomsRemaining(),
            ],
            'purpose'  => 'required|in:classroom,lab,library,office,other',
            'hoi_note' => 'nullable|string|max:500',
        ], [
            'rooms_assigned.max' => 'Only ' . $construction->roomsRemaining() . ' room(s) remaining to allocate.',
        ]);

        // Prevent duplicate class allocation
        $alreadyAllocated = RoomAllocation::where('institution_id', $institution->id)
            ->where('class_id', $request->class_id)
            ->exists();

        if ($alreadyAllocated) {
            return back()->withErrors(['class_id' => 'Rooms already allocated for this class.']);
        }

        // Ensure class is configured at this institution
        $classConfigured = InstitutionClass::where('institution_id', $institution->id)
            ->where('class_id', $request->class_id)
            ->where('is_active', true)
            ->exists();

        if (! $classConfigured) {
            return back()->withErrors(['class_id' => 'This class is not configured for your school.']);
        }

        DB::transaction(function () use ($construction, $institution, $request) {
            RoomAllocation::create([
                'new_construction_room_id' => $construction->id,
                'institution_id'           => $institution->id,
                'class_id'                 => $request->class_id,
                'rooms_assigned'           => $request->rooms_assigned,
                'purpose'                  => $request->purpose,
                'hoi_note'                 => $request->hoi_note,
                'status'                   => 'pending',
            ]);

            // Update total rooms_allocated on the parent record
            $construction->increment('rooms_allocated', $request->rooms_assigned);
        });

        return back()->with('success', 'Room allocation submitted for FDE review.');
    }

    // ── Update an existing pending allocation ─────────────────────────
    public function update(Request $request, RoomAllocation $allocation)
    {
        $institution = Auth::user()->institution;
        abort_if($allocation->institution_id !== $institution->id, 403);
        abort_if(! $allocation->isPending(), 422, 'Only pending allocations can be edited.');

        $construction = $allocation->newConstructionRoom;
        $available    = $construction->roomsRemaining() + $allocation->rooms_assigned; // add back current

        $request->validate([
            'rooms_assigned' => ['required', 'integer', 'min:1', 'max:' . $available],
            'purpose'        => 'required|in:classroom,lab,library,office,other',
            'hoi_note'       => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($allocation, $request, $construction) {
            $diff = $request->rooms_assigned - $allocation->rooms_assigned;

            $allocation->update([
                'rooms_assigned' => $request->rooms_assigned,
                'purpose'        => $request->purpose,
                'hoi_note'       => $request->hoi_note,
            ]);

            $construction->increment('rooms_allocated', $diff);
        });

        return back()->with('success', 'Allocation updated.');
    }

    // ── Export PDF — HOI's own school only ───────────────────────────
    public function exportPdf()
    {
        $institution = Auth::user()->institution;
        abort_if(! $institution, 403, 'No institution assigned.');

        // Scope export to this HOI's school only
        $records = NewConstructionRoom::with(['institution.sector'])
            ->where('institution_id', $institution->id)
            ->get();

        $generatedAt = now()->format('d M Y, h:i A');
        $search      = null;
        $status      = null;

        $pdf = Pdf::loadView('fde.rooms.pdf', compact('records', 'generatedAt', 'search', 'status'))
                  ->setPaper('a4', 'landscape');

        $filename = 'room-allocation-' . str($institution->name)->slug() . '-' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    // ── Delete a pending allocation ───────────────────────────────────
    public function destroy(RoomAllocation $allocation)
    {
        $institution = Auth::user()->institution;
        abort_if($allocation->institution_id !== $institution->id, 403);
        abort_if(! $allocation->isPending(), 422, 'Only pending allocations can be removed.');

        DB::transaction(function () use ($allocation) {
            $allocation->newConstructionRoom->decrement('rooms_allocated', $allocation->rooms_assigned);
            $allocation->delete();
        });

        return back()->with('success', 'Allocation removed.');
    }
}

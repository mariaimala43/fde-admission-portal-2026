<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Institution;
use App\Models\Sector;
use App\Models\UnionCouncil;
use App\Models\AuditLog;

class InstitutionController extends Controller
{
    // ── List all institutions ──────────────────────────────
    public function index(Request $request)
    {
        $query = Institution::with(['sector', 'unionCouncil'])
            ->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('sector')) {
            if ($request->sector === 'model_colleges') {
                $query->where('type', 'Model College');
            } else {
                $query->where('sector_id', $request->sector);
            }
        }

        $institutions = $query->paginate(20)->withQueryString();
        $sectors      = \App\Models\Sector::orderBy('name')->get();

        return view('admin.institutions.index', compact('institutions', 'sectors'));
    }

    // ── Show create form ───────────────────────────────────
    public function create()
    {
        // Load sectors with their UCs for grouped dropdown + auto sector derivation
        $sectors = Sector::with(['unionCouncils' => fn($q) => $q->where('is_active', true)->orderBy('code')])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.institutions.create', compact('sectors'));
    }

    // ── Store new institution ──────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'code'   => 'nullable|string|max:50|unique:institutions,code',
            'uc_id'  => 'required|exists:union_councils,id',
            'type'   => 'required|in:I-V,I-VIII,I-X,I-XII,VI-VIII,VI-X,VI-XII,XI-XII,XI-XIV,Model College',
            'gender' => 'required|in:boys,girls,co_education',
            'shift'  => 'required|in:morning,evening,both',
            'address'=> 'nullable|string',
        ]);

        // Sector is always derived from the UC — never from a separate form input
        $uc       = UnionCouncil::findOrFail($request->uc_id);
        $sectorId = $uc->sector_id;

        // Derive has_evening_classes from shift — single source of truth
        $hasEveningClasses = in_array($request->shift, ['evening', 'both']);

        $institution = Institution::create([
            'name'               => $request->name,
            'code'               => $request->code,
            'sector_id'          => $sectorId,
            'uc_id'              => $request->uc_id,
            'type'               => $request->type,
            'gender'             => $request->gender,
            'shift'              => $request->shift,
            'address'            => $request->address,
            'has_matric_tech'    => $request->boolean('has_matric_tech'),
            'has_transport'      => $request->boolean('has_transport'),
            'has_meal_program'   => $request->boolean('has_meal_program'),
            'has_evening_classes'=> $hasEveningClasses,
            'admission_status'   => 'not_started',
            'is_active'          => true,
        ]);

        // Set cambridge status based on name — system enforced
        if ($institution->isCambridgeEligible()) {
            \DB::table('institutions')
                ->where('id', $institution->id)
                ->update(['is_cambridge' => true]);
        }

        AuditLog::record(
            action: 'created',
            modelType: 'Institution',
            modelId: $institution->id,
            newValues: $institution->toArray(),
            institutionId: $institution->id
        );

        return redirect()->route('admin.institutions.index')
            ->with('success', 'Institution created successfully.');
    }

    // ── Show single institution ────────────────────────────
    public function show(Institution $institution)
    {
        $institution->load(['sector', 'unionCouncil', 'sections']);

        return view('admin.institutions.show', compact('institution'));
    }

    // ── Show edit form ─────────────────────────────────────
    public function edit(Institution $institution)
    {
        $institution->load('unionCouncil.sector');

        $sectors = Sector::with(['unionCouncils' => fn($q) => $q->where('is_active', true)->orderBy('code')])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.institutions.edit', compact('institution', 'sectors'));
    }

    // ── Delete institution ─────────────────────────────────
    public function destroy(Institution $institution)
    {
        // Block if institution has any users (HOI assigned)
        if ($institution->users()->exists()) {
            return back()->with('error', "Cannot delete: \"{$institution->name}\" has users assigned to it. Remove the HOI user first.");
        }

        // Block if institution has any admission data
        if ($institution->dailyAdmissions()->exists()) {
            return back()->with('error', "Cannot delete: \"{$institution->name}\" has admission records. Deactivate instead.");
        }

        $name = $institution->name;

        AuditLog::record(
            action: 'deleted',
            modelType: 'Institution',
            modelId: $institution->id,
            oldValues: $institution->toArray(),
            institutionId: $institution->id
        );

        $institution->delete();

        return redirect()->route('admin.institutions.index')
            ->with('success', "Institution \"{$name}\" has been deleted.");
    }

    // ── Update institution ─────────────────────────────────
    public function update(Request $request, Institution $institution)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'code'   => 'nullable|string|max:50|unique:institutions,code,' . $institution->id,
            'uc_id'  => 'required|exists:union_councils,id',
            'type'   => 'required|in:I-V,I-VIII,I-X,I-XII,VI-VIII,VI-X,VI-XII,XI-XII,XI-XIV,Model College',
            'gender' => 'required|in:boys,girls,co_education',
            'shift'  => 'required|in:morning,evening,both',
            'address'=> 'nullable|string',
        ]);

        // Sector always derived from UC
        $uc       = UnionCouncil::findOrFail($request->uc_id);
        $sectorId = $uc->sector_id;

        $old = $institution->toArray();

        // Derive has_evening_classes from shift — single source of truth
        $hasEveningClasses = in_array($request->shift, ['evening', 'both']);

        $institution->update([
            'name'               => $request->name,
            'code'               => $request->code,
            'sector_id'          => $sectorId,
            'uc_id'              => $request->uc_id,
            'type'               => $request->type,
            'gender'             => $request->gender,
            'shift'              => $request->shift,
            'address'            => $request->address,
            'has_matric_tech'    => $request->boolean('has_matric_tech'),
            'has_transport'      => $request->boolean('has_transport'),
            'has_meal_program'   => $request->boolean('has_meal_program'),
            'has_evening_classes'=> $hasEveningClasses,
        ]);

        AuditLog::record(
            action: 'updated',
            modelType: 'Institution',
            modelId: $institution->id,
            oldValues: $old,
            newValues: $institution->fresh()->toArray(),
            institutionId: $institution->id
        );

        return redirect()->route('admin.institutions.index')
            ->with('success', 'Institution updated successfully.');
    }
}

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
        $query = Institution::with(['sector'])
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
            $query->where('sector_id', $request->sector);
        }

        $institutions = $query->paginate(20)->withQueryString();
        $sectors      = \App\Models\Sector::orderBy('name')->get();

        return view('admin.institutions.index', compact('institutions', 'sectors'));
    }

    // ── Show create form ───────────────────────────────────
    public function create()
    {
        $ucs     = UnionCouncil::where('is_active', true)->orderBy('name')->get();
        $sectors = Sector::where('is_active', true)->orderBy('name')->get();

        return view('admin.institutions.create', compact('ucs', 'sectors'));
    }

    // ── Store new institution ──────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'nullable|string|max:50|unique:institutions,code',
            'sector_id' => 'required|exists:sectors,id',
            'uc_id'     => 'required|exists:union_councils,id',
            'type'      => 'required|in:I-V,I-VIII,I-X,I-XII,VI-VIII,VI-X,VI-XII,Model College',
            'gender'    => 'required|in:boys,girls,co_education',
            'shift'     => 'required|in:morning,evening,both',
            'address'   => 'nullable|string',
        ]);

        $institution = Institution::create([
            'name'               => $request->name,
            'code'               => $request->code,
            'sector_id'          => $request->sector_id,
            'uc_id'              => $request->uc_id,
            'type'               => $request->type,
            'gender'             => $request->gender,
            'shift'              => $request->shift,
            'address'            => $request->address,
            'has_matric_tech'    => $request->boolean('has_matric_tech'),
            'has_transport'      => $request->boolean('has_transport'),
            'has_meal_program'   => $request->boolean('has_meal_program'),
            'has_evening_classes'=> $request->boolean('has_evening_classes'),
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
        $ucs     = UnionCouncil::where('is_active', true)->orderBy('name')->get();
        $sectors = Sector::where('is_active', true)->orderBy('name')->get();

        return view('admin.institutions.edit', compact('institution', 'ucs', 'sectors'));
    }

    // ── Update institution ─────────────────────────────────
    public function update(Request $request, Institution $institution)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'nullable|string|max:50|unique:institutions,code,' . $institution->id,
            'sector_id' => 'required|exists:sectors,id',
            'uc_id'     => 'required|exists:union_councils,id',
            'type'      => 'required|in:I-V,I-VIII,I-X,I-XII,VI-VIII,VI-X,VI-XII,Model College',
            'gender'    => 'required|in:boys,girls,co_education',
            'shift'     => 'required|in:morning,evening,both',
            'address'   => 'nullable|string',
        ]);

        $old = $institution->toArray();

        $institution->update([
            'name'               => $request->name,
            'code'               => $request->code,
            'sector_id'          => $request->sector_id,
            'uc_id'              => $request->uc_id,
            'type'               => $request->type,
            'gender'             => $request->gender,
            'shift'              => $request->shift,
            'address'            => $request->address,
            'has_matric_tech'    => $request->boolean('has_matric_tech'),
            'has_transport'      => $request->boolean('has_transport'),
            'has_meal_program'   => $request->boolean('has_meal_program'),
            'has_evening_classes'=> $request->boolean('has_evening_classes'),
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

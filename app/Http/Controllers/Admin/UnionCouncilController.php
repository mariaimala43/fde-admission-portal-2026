<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UnionCouncil;
use App\Models\Institution;
use App\Models\Sector;
use App\Models\AuditLog;

class UnionCouncilController extends Controller
{
    // ── List all UCs ───────────────────────────────────────
    public function index()
    {
        $ucs = UnionCouncil::with('sector')
            ->withCount('institutions')
            ->orderBy('code')
            ->paginate(60);   // show all 54 UCs on one page

        return view('admin.ucs.index', compact('ucs'));
    }

    // ── Show create form ───────────────────────────────────
    public function create()
    {
        $sectors = Sector::where('is_active', true)->orderBy('name')->get();
        return view('admin.ucs.create', compact('sectors'));
    }

    // ── Store new UC ───────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:50|unique:union_councils,code',
            'sector_id' => 'required|exists:sectors,id',
        ]);

        $uc = UnionCouncil::create([
            'name'      => $request->name,
            'code'      => strtoupper($request->code),
            'sector_id' => $request->sector_id,
            'is_active' => true,
        ]);

        AuditLog::record(
            action: 'created',
            modelType: 'UnionCouncil',
            modelId: $uc->id,
            newValues: $uc->toArray()
        );

        return redirect()->route('admin.ucs.index')
            ->with('success', 'Union Council created successfully.');
    }

    // ── Show edit form ─────────────────────────────────────
    public function edit(UnionCouncil $unionCouncil)
    {
        $sectors = Sector::where('is_active', true)->orderBy('name')->get();
        return view('admin.ucs.edit', compact('unionCouncil', 'sectors'));
    }

    // ── Update UC ──────────────────────────────────────────
    public function update(Request $request, UnionCouncil $unionCouncil)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:50|unique:union_councils,code,' . $unionCouncil->id,
            'sector_id' => 'required|exists:sectors,id',
        ]);

        $old           = $unionCouncil->toArray();
        $sectorChanged = (int) $request->sector_id !== (int) $unionCouncil->sector_id;

        $unionCouncil->update([
            'name'      => $request->name,
            'code'      => strtoupper($request->code),
            'sector_id' => $request->sector_id,
        ]);

        // Cascade sector change to all institutions under this UC.
        // Hierarchy rule: institution.sector_id always equals its UC's sector_id.
        if ($sectorChanged) {
            Institution::where('uc_id', $unionCouncil->id)
                ->update(['sector_id' => $request->sector_id]);
        }

        AuditLog::record(
            action: 'updated',
            modelType: 'UnionCouncil',
            modelId: $unionCouncil->id,
            oldValues: $old,
            newValues: $unionCouncil->fresh()->toArray()
        );

        $successMsg = $sectorChanged
            ? 'Union Council updated. Sector cascaded to all linked institutions.'
            : 'Union Council updated successfully.';

        return redirect()->route('admin.ucs.index')
            ->with('success', $successMsg);
    }

    // ── Delete UC ──────────────────────────────────────────
    public function destroy(UnionCouncil $unionCouncil)
    {
        if ($unionCouncil->institutions()->exists()) {
            return back()->with('error', "Cannot delete: Union Council \"{$unionCouncil->name}\" has institutions assigned to it.");
        }

        $name = $unionCouncil->name;

        AuditLog::record(
            action: 'deleted',
            modelType: 'UnionCouncil',
            modelId: $unionCouncil->id,
            oldValues: $unionCouncil->toArray()
        );

        $unionCouncil->delete();

        return redirect()->route('admin.ucs.index')
            ->with('success', "Union Council \"{$name}\" has been deleted.");
    }
}

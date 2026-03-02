<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sector;
use App\Models\UnionCouncil;
use App\Models\AuditLog;

class SectorController extends Controller
{
    // ── List all sectors ───────────────────────────────────
    public function index()
    {
       $sectors = Sector::with('unionCouncil')
                    ->withCount('institutions')
                    ->orderBy('name')
                    ->paginate(20);
        return view('admin.sectors.index', compact('sectors'));
    }

    // ── Show create form ───────────────────────────────────
    public function create()
    {
        $ucs = UnionCouncil::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.sectors.create', compact('ucs'));
    }

    // ── Store new sector ───────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'uc_id' => 'required|exists:union_councils,id',
            'name'  => 'required|string|max:255',
            'code'  => 'required|string|max:50|unique:sectors,code',
        ]);

        $sector = Sector::create([
            'uc_id'     => $request->uc_id,
            'name'      => $request->name,
            'code'      => strtoupper($request->code),
            'is_active' => true,
        ]);

        AuditLog::record(
            action: 'created',
            modelType: 'Sector',
            modelId: $sector->id,
            newValues: $sector->toArray()
        );

        return redirect()->route('admin.sectors.index')
            ->with('success', 'Sector created successfully.');
    }

    // ── Show edit form ─────────────────────────────────────
    public function edit(Sector $sector)
    {
        $ucs = UnionCouncil::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.sectors.edit', compact('sector', 'ucs'));
    }

    // ── Update sector ──────────────────────────────────────
    public function update(Request $request, Sector $sector)
    {
        $request->validate([
            'uc_id' => 'required|exists:union_councils,id',
            'name'  => 'required|string|max:255',
            'code'  => 'required|string|max:50|unique:sectors,code,' . $sector->id,
        ]);

        $old = $sector->toArray();

        $sector->update([
            'uc_id' => $request->uc_id,
            'name'  => $request->name,
            'code'  => strtoupper($request->code),
        ]);

        AuditLog::record(
            action: 'updated',
            modelType: 'Sector',
            modelId: $sector->id,
            oldValues: $old,
            newValues: $sector->fresh()->toArray()
        );

        return redirect()->route('admin.sectors.index')
            ->with('success', 'Sector updated successfully.');
    }
}

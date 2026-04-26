<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sector;
use App\Models\AuditLog;

class SectorController extends Controller
{
    // ── List all sectors ───────────────────────────────────
    public function index()
    {
        $sectors = Sector::withCount(['institutions', 'unionCouncils'])
            ->orderBy('name')
            ->paginate(20);

        return view('admin.sectors.index', compact('sectors'));
    }

    // ── Show create form ───────────────────────────────────
    public function create()
    {
        return view('admin.sectors.create');
    }

    // ── Store new sector ───────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:sectors,code',
        ]);

        $sector = Sector::create([
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
        return view('admin.sectors.edit', compact('sector'));
    }

    // ── Update sector ──────────────────────────────────────
    public function update(Request $request, Sector $sector)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:sectors,code,' . $sector->id,
        ]);

        $old = $sector->toArray();

        $sector->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
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

    // ── Delete sector ──────────────────────────────────────
    public function destroy(Sector $sector)
    {
        if ($sector->institutions()->exists()) {
            return back()->with('error', "Cannot delete: Sector \"{$sector->name}\" has {$sector->institutions()->count()} institution(s) assigned to it.");
        }

        $name = $sector->name;

        AuditLog::record(
            action: 'deleted',
            modelType: 'Sector',
            modelId: $sector->id,
            oldValues: $sector->toArray()
        );

        $sector->delete();

        return redirect()->route('admin.sectors.index')
            ->with('success', "Sector \"{$name}\" has been deleted.");
    }
}

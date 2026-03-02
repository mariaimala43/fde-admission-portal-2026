<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UnionCouncil;
use App\Models\AuditLog;

class UnionCouncilController extends Controller
{
    // ── List all UCs ───────────────────────────────────────
    public function index()
    {
        $ucs = UnionCouncil::withCount('sectors')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.ucs.index', compact('ucs'));
    }

    // ── Show create form ───────────────────────────────────
    public function create()
    {
        return view('admin.ucs.create');
    }

    // ── Store new UC ───────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:union_councils,code',
        ]);

        $uc = UnionCouncil::create([
            'name'      => $request->name,
            'code'      => strtoupper($request->code),
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
        return view('admin.ucs.edit', compact('unionCouncil'));
    }

    // ── Update UC ──────────────────────────────────────────
    public function update(Request $request, UnionCouncil $unionCouncil)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:union_councils,code,' . $unionCouncil->id,
        ]);

        $old = $unionCouncil->toArray();

        $unionCouncil->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
        ]);

        AuditLog::record(
            action: 'updated',
            modelType: 'UnionCouncil',
            modelId: $unionCouncil->id,
            oldValues: $old,
            newValues: $unionCouncil->fresh()->toArray()
        );

        return redirect()->route('admin.ucs.index')
            ->with('success', 'Union Council updated successfully.');
    }
}

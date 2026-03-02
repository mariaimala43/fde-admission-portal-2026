<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UnionCouncil;
use App\Models\Sector;
use App\Models\Institution;
use App\Models\AuditLog;

class ProfileController extends Controller
{
    // ── Show setup form ────────────────────────────────────
    public function setup()
    {
        $user = Auth::user();

        // If already set up, redirect to dashboard
        if ($user->institution_id) {
            return redirect()->route('dashboard');
        }

        $ucs = UnionCouncil::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('hoi.profile.setup', compact('ucs'));
    }

    // ── Save setup ─────────────────────────────────────────
    public function saveSetup(Request $request)
    {
        $request->validate([
            'uc_id'          => 'required|exists:union_councils,id',
            'sector_id'      => 'required|exists:sectors,id',
            'institution_id' => 'required|exists:institutions,id',
            'gender'         => 'required|in:boys,girls,co_education',
            'shift'          => 'required|in:morning,evening,both',
        ]);

        $user        = Auth::user();
        $institution = Institution::findOrFail($request->institution_id);

        // Check institution belongs to selected sector
        if ($institution->sector_id != $request->sector_id) {
            return back()->withErrors([
                'institution_id' => 'Selected school does not belong to the selected sector.'
            ]);
        }

        // Update institution gender and shift
        $institution->update([
            'gender' => $request->gender,
            'shift'  => $request->shift,
        ]);

        // Link user to institution
        $user->update([
            'institution_id' => $institution->id,
        ]);

        AuditLog::record(
            action: 'updated',
            modelType: 'User',
            modelId: $user->id,
            newValues: ['institution_id' => $institution->id],
            institutionId: $institution->id
        );

        return redirect()->route('dashboard')
            ->with('success', 'Profile setup complete. Welcome, ' . $user->name . '!');
    }

    // ── AJAX: Get sectors by UC ────────────────────────────
   public function getSectors(Request $request)
    {
        // Find which sector this UC belongs to
        $uc = \App\Models\UnionCouncil::with('sector')
            ->find($request->uc_id);

        if (!$uc || !$uc->sector) {
            return response()->json([]);
        }

        // Return that sector as array
        return response()->json([
            [
                'id'   => $uc->sector->id,
                'name' => $uc->sector->name,
                'code' => $uc->sector->code,
            ]
        ]);
    }

    // ── AJAX: Get institutions by sector ───────────────────
    public function getInstitutions(Request $request)
    {
        $institutions = Institution::where('sector_id', $request->sector_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'gender', 'shift']);

        return response()->json($institutions);
    }
}

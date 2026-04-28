<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\InstitutionClass;

class FacilityController extends Controller
{
    public function index()
    {
        $institution = Auth::user()->institution;

        if (! $institution) {
            return redirect()->route('hoi.profile.setup');
        }

        return view('hoi.facilities.index', compact('institution'));
    }

    public function save(Request $request)
    {
        $institution = Auth::user()->institution;

        abort_if(! $institution, 403, 'No institution assigned.');

        $request->validate([
            'has_transport'       => 'boolean',
            'has_meal_program'    => 'boolean',
            'has_matric_tech'     => 'boolean',
            'has_evening_classes' => 'boolean',
        ]);

        $hasEvening    = $request->boolean('has_evening_classes');
        $wasEvening    = (bool) $institution->has_evening_classes;

        // Derive the canonical `shift` value from the boolean flag so both fields
        // always stay in sync. Evening-only schools ('evening') are treated as 'both'
        // since FDE schools always run a morning shift alongside any evening shift.
        $shift = $hasEvening ? 'both' : 'morning';

        $institution->update([
            'has_transport'       => $request->boolean('has_transport'),
            'has_meal_program'    => $request->boolean('has_meal_program'),
            'has_matric_tech'     => $request->boolean('has_matric_tech'),
            'has_evening_classes' => $hasEvening,
            'shift'               => $shift,
        ]);

        // ── Evening shift turned OFF ───────────────────────────────────
        // Reset all per-shift columns back to morning-only values so the
        // Class Setup form pre-populates correctly (total_seats / existing_enrollment
        // were previously the combined morning+evening sum).
        if ($wasEvening && ! $hasEvening) {
            InstitutionClass::where('institution_id', $institution->id)
                ->each(function (InstitutionClass $ic) {
                    $ic->update([
                        // Collapse combined fields back to morning-only
                        'total_seats'         => $ic->morning_seats,
                        'existing_enrollment' => $ic->morning_existing,
                        'promoted_count'      => $ic->morning_promoted,
                        'failed_count'        => $ic->morning_failed,
                        // Zero out all evening-specific columns
                        'morning_seats'       => 0,
                        'evening_seats'       => 0,
                        'morning_existing'    => 0,
                        'evening_existing'    => 0,
                        'morning_promoted'    => 0,
                        'evening_promoted'    => 0,
                        'morning_failed'      => 0,
                        'evening_failed'      => 0,
                    ]);
                });
        }

        return redirect()->route('hoi.facilities.index')
            ->with('success', 'Facility settings saved successfully.');
    }
}

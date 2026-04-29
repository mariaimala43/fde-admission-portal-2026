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
        // Clear evening-specific columns. If per-shift seats were actually
        // configured (morning_seats > 0), collapse morning_seats → total_seats
        // so the Class Setup form reflects morning-only capacity. If per-shift
        // columns were never filled (school set up classes before enabling
        // evening), keep the existing total_seats / existing_enrollment intact
        // so no morning data is lost.
        if ($wasEvening && ! $hasEvening) {
            InstitutionClass::where('institution_id', $institution->id)
                ->each(function (InstitutionClass $ic) {
                    $ic->update([
                        // Use morning-specific value when available; otherwise
                        // preserve the existing combined value to avoid data loss.
                        'total_seats'         => $ic->morning_seats > 0
                                                    ? $ic->morning_seats
                                                    : $ic->total_seats,
                        'existing_enrollment' => $ic->morning_existing > 0
                                                    ? $ic->morning_existing
                                                    : $ic->existing_enrollment,
                        'promoted_count'      => $ic->morning_promoted > 0
                                                    ? $ic->morning_promoted
                                                    : $ic->promoted_count,
                        'failed_count'        => $ic->morning_failed > 0
                                                    ? $ic->morning_failed
                                                    : $ic->failed_count,
                        // Zero out all per-shift columns (both shifts)
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

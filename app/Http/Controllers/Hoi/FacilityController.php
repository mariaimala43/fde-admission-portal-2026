<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $institution->update([
            'has_transport'       => $request->boolean('has_transport'),
            'has_meal_program'    => $request->boolean('has_meal_program'),
            'has_matric_tech'     => $request->boolean('has_matric_tech'),
            'has_evening_classes' => $request->boolean('has_evening_classes'),
        ]);

        return redirect()->route('hoi.facilities.index')
            ->with('success', 'Facility settings saved successfully.');
    }
}

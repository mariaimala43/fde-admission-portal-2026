<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Classes;
use App\Models\InstitutionClass;
use App\Models\InstitutionSection;
use App\Helpers\SchoolClassHelper;

class ClassSetupController extends Controller
{
    public function index()
    {
        $user        = Auth::user();
        $institution = $user->institution;

        if (!$institution) {
            return redirect()->route('hoi.profile.setup');
        }

        // Get allowed class orders for this school type
        $allowedOrders = SchoolClassHelper::allowedClassOrders($institution->type);

        // Get all regular classes for this school type
        $classes = Classes::whereIn('order', $allowedOrders)
            ->where('is_ece', false)
            ->orderBy('order')
            ->get();

        // Get ECE classes
        $eceClasses = Classes::where('is_ece', true)
            ->orderBy('order')
            ->get();

        // Get already configured institution classes
        $configured = InstitutionClass::where('institution_id', $institution->id)
            ->with(['classModel'])
            ->get()
            ->keyBy('class_id');

        // Get already configured sections
        $sections = InstitutionSection::where('institution_id', $institution->id)
            ->orderBy('class_id')
            ->orderBy('order')
            ->get()
            ->groupBy('class_id');

        return view('hoi.classes.setup', compact(
            'institution', 'classes', 'eceClasses',
            'configured', 'sections'
        ));
    }

    public function save(Request $request)
    {
        $user        = Auth::user();
        $institution = $user->institution;

        if (!$institution) {
            return redirect()->route('hoi.profile.setup');
        }

        $request->validate([
            'classes'                    => 'nullable|array',
            'classes.*.class_id'         => 'required|exists:classes,id',
            'classes.*.total_seats'      => 'required|integer|min:0|max:9999',
            'classes.*.sections'         => 'nullable|string',
            'has_ece'                    => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $institution) {

            // ── ECE toggle ─────────────────────────────
            $institution->update([
                'has_ece' => $request->boolean('has_ece'),
            ]);

            // ── Remove old config ──────────────────────
            InstitutionClass::where('institution_id', $institution->id)->delete();
            InstitutionSection::where('institution_id', $institution->id)->delete();

            // ── Save classes + sections ────────────────
            $allowedOrders = SchoolClassHelper::allowedClassOrders($institution->type);
            $allowedIds    = Classes::whereIn('order', $allowedOrders)
                ->pluck('id')->toArray();

            // Also allow ECE class IDs if has_ece
            if ($request->boolean('has_ece')) {
                $eceIds     = Classes::where('is_ece', true)->pluck('id')->toArray();
                $allowedIds = array_merge($allowedIds, $eceIds);
            }

            foreach ($request->input('classes', []) as $item) {
                $classId = (int) $item['class_id'];

                if (!in_array($classId, $allowedIds)) continue;

                // Save institution class
                InstitutionClass::create([
                    'institution_id' => $institution->id,
                    'class_id'       => $classId,
                    'total_seats'    => (int) $item['total_seats'],
                    'is_active'      => true,
                ]);

                // Save sections — comma separated e.g. "A,B,C"
                // Default to 1 section "A" if no sections specified
                $sectionNames = array_filter(
                    array_map('trim', explode(',', strtoupper($item['sections'] ?? '')))
                );

                if (empty($sectionNames)) {
                    $sectionNames = ['A'];
                }

                foreach (array_values($sectionNames) as $i => $name) {
                    if (!$name) continue;
                    InstitutionSection::create([
                        'institution_id' => $institution->id,
                        'class_id'       => $classId,
                        'name'           => $name,
                        'order'          => $i + 1,
                        'is_active'      => true,
                    ]);
                }
            }
            // Mark institution as configured
            $institution->update(['classes_configured' => true]);
        });

        return redirect()->route('hoi.classes.setup')
            ->with('success', 'Classes and sections saved successfully.');
    }
}

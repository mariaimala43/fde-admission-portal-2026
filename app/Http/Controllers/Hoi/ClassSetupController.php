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

        $hasEvening = (bool) $institution->has_evening_classes;

        return view('hoi.classes.setup', compact(
            'institution', 'classes', 'eceClasses',
            'configured', 'sections', 'hasEvening'
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
            'classes'                       => 'nullable|array',
            'classes.*.class_id'            => 'required|exists:classes,id',
            'classes.*.active'              => 'nullable|in:0,1',
            'classes.*.total_seats'         => 'nullable|integer|min:0|max:9999',
            'classes.*.morning_seats'       => 'nullable|integer|min:0|max:9999',
            'classes.*.evening_seats'       => 'nullable|integer|min:0|max:9999',
            'classes.*.existing_enrollment' => 'nullable|integer|min:0|max:99999',
            'classes.*.morning_existing'    => 'nullable|integer|min:0|max:99999',
            'classes.*.evening_existing'    => 'nullable|integer|min:0|max:99999',
            'classes.*.sections'             => 'nullable|string',
            'classes.*.matric_tech_existing' => 'nullable|integer|min:0|max:99999',
            'has_ece'                        => 'nullable|boolean',
        ]);

        // Preload class order map for validation and saving
        $classOrderMap = Classes::pluck('order', 'id'); // [class_id => order]

        // Extra check: seats must be >= existing for each active class
        foreach ($request->input('classes', []) as $item) {
            if (($item['active'] ?? '1') === '0') continue; // skip unchecked

            $classId    = (int) ($item['class_id'] ?? 0);
            $classOrder = $classOrderMap[$classId] ?? 0;

            if ($institution->has_evening_classes) {
                $mSeats = (int) ($item['morning_seats']    ?? 0);
                $eSeats = (int) ($item['evening_seats']    ?? 0);
                $mExist = (int) ($item['morning_existing'] ?? 0);
                $eExist = (int) ($item['evening_existing'] ?? 0);
                if (($mSeats > 0 && $mSeats < $mExist) || ($eSeats > 0 && $eSeats < $eExist)) {
                    return back()->withInput()->withErrors([
                        'classes' => 'Seats cannot be less than Existing Students for any shift. Please correct the highlighted rows.',
                    ]);
                }
                // Matric Tech cross-validation (evening: against combined existing)
                if ($institution->has_matric_tech && in_array($classOrder, [9, 10])) {
                    $mtExisting = (int) ($item['matric_tech_existing'] ?? 0);
                    if ($mtExisting > ($mExist + $eExist)) {
                        return back()->withInput()->withErrors([
                            'classes' => 'Matric Tech existing students cannot exceed total existing students for Class 9 or 10.',
                        ]);
                    }
                }
            } else {
                $total    = (int) ($item['total_seats']         ?? 0);
                $existing = (int) ($item['existing_enrollment'] ?? 0);
                if ($total > 0 && $total < $existing) {
                    return back()->withInput()->withErrors([
                        'classes' => 'Total Seats cannot be less than Existing Students for any class. Please correct the highlighted rows.',
                    ]);
                }
                // Matric Tech cross-validation
                if ($institution->has_matric_tech && in_array($classOrder, [9, 10])) {
                    $mtExisting = (int) ($item['matric_tech_existing'] ?? 0);
                    if ($mtExisting > $existing) {
                        return back()->withInput()->withErrors([
                            'classes' => 'Matric Tech existing students cannot exceed total existing students for Class 9 or 10.',
                        ]);
                    }
                }
            }
        }

        DB::transaction(function () use ($request, $institution, $classOrderMap) {

            // ── ECE toggle ─────────────────────────────
            $institution->update([
                'has_ece' => $request->boolean('has_ece'),
            ]);

            // ── Determine allowed class IDs ────────────
            $allowedOrders = SchoolClassHelper::allowedClassOrders($institution->type);
            $allowedIds    = Classes::whereIn('order', $allowedOrders)
                ->pluck('id')->toArray();

            // Always load ECE IDs so we can detect ECE rows in the save loop
            $eceIds = Classes::where('is_ece', true)->pluck('id')->toArray();

            if ($request->boolean('has_ece')) {
                $allowedIds = array_merge($allowedIds, $eceIds);
            }

            // ── Sections: delete and recreate (they always change) ──
            InstitutionSection::where('institution_id', $institution->id)->delete();

            $submittedIds = [];

            foreach ($request->input('classes', []) as $item) {
                $classId    = (int) $item['class_id'];
                $isActive   = ($item['active'] ?? '1') !== '0';
                $classOrder = $classOrderMap[$classId] ?? 0;

                if (!in_array($classId, $allowedIds)) continue;

                // Unchecked class — deactivate and skip
                if (!$isActive) {
                    InstitutionClass::where('institution_id', $institution->id)
                        ->where('class_id', $classId)
                        ->update(['is_active' => false]);
                    continue;
                }

                $submittedIds[] = $classId;

                // Matric Tech existing — only for Class 9 & 10 when enabled
                $isMatricTechClass  = $institution->has_matric_tech && in_array($classOrder, [9, 10]);
                $matricTechExisting = $isMatricTechClass ? (int) ($item['matric_tech_existing'] ?? 0) : 0;

                // updateOrCreate preserves enrollment_status and daily admission data
                if ($institution->has_evening_classes) {
                    $mSeats    = (int) ($item['morning_seats']    ?? 0);
                    $eSeats    = (int) ($item['evening_seats']    ?? 0);
                    $mExisting = (int) ($item['morning_existing'] ?? 0);
                    $eExisting = (int) ($item['evening_existing'] ?? 0);

                    InstitutionClass::updateOrCreate(
                        [
                            'institution_id' => $institution->id,
                            'class_id'       => $classId,
                        ],
                        [
                            'morning_seats'        => $mSeats,
                            'evening_seats'        => $eSeats,
                            'total_seats'          => $mSeats + $eSeats,
                            'morning_existing'     => $mExisting,
                            'evening_existing'     => $eExisting,
                            'existing_enrollment'  => $mExisting + $eExisting,
                            'matric_tech_existing' => $matricTechExisting,
                            'is_active'            => true,
                        ]
                    );
                } else {
                    InstitutionClass::updateOrCreate(
                        [
                            'institution_id' => $institution->id,
                            'class_id'       => $classId,
                        ],
                        [
                            'total_seats'          => (int) ($item['total_seats'] ?? 0),
                            'existing_enrollment'  => (int) ($item['existing_enrollment'] ?? 0),
                            'matric_tech_existing' => $matricTechExisting,
                            'is_active'            => true,
                        ]
                    );
                }

                // ── Sections ──
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

            // ── Deactivate classes not in this submission ──
            if (!empty($submittedIds)) {
                InstitutionClass::where('institution_id', $institution->id)
                    ->whereNotIn('class_id', $submittedIds)
                    ->update(['is_active' => false]);
            }

            $institution->update(['classes_configured' => true]);
        });

        return redirect()->route('hoi.classes.setup')
            ->with('success', 'Classes and sections saved successfully.');
    }
}

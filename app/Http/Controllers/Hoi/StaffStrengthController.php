<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;
use App\Models\StaffPostType;
use App\Models\StaffStrengthRegister;
use App\Models\StaffStrengthEntry;

class StaffStrengthController extends Controller
{
    public function index()
    {
        $user        = Auth::user();
        $institution = $user->institution;

        if (! $institution) {
            return redirect()->route('hoi.profile.setup');
        }

        $academicYear = AcademicYear::where('is_active', true)->first();

        if (! $academicYear) {
            return back()->with('error', 'No active academic year found.');
        }

        // Auto-create a draft register on first visit
        $register = StaffStrengthRegister::firstOrCreate(
            [
                'institution_id'  => $institution->id,
                'academic_year_id'=> $academicYear->id,
            ],
            ['status' => 'draft']
        );

        // Load post types applicable to this institution's type (school level)
        $teachingTypes = StaffPostType::forLevel($institution->type)
            ->where('section', 'teaching')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $programTypes = StaffPostType::where('section', 'program')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Index existing entries by post_type_id for quick lookup
        $entries = $register->entries()->with('postType')->get()->keyBy('post_type_id');

        return view('hoi.staff-strength.index', compact(
            'institution',
            'academicYear',
            'register',
            'teachingTypes',
            'programTypes',
            'entries',
        ));
    }

    public function save(Request $request)
    {
        $user        = Auth::user();
        $institution = $user->institution;

        if (! $institution) {
            return redirect()->route('hoi.profile.setup');
        }

        $academicYear = AcademicYear::where('is_active', true)->first();

        if (! $academicYear) {
            return back()->with('error', 'No active academic year found.');
        }

        $register = StaffStrengthRegister::where('institution_id', $institution->id)
            ->where('academic_year_id', $academicYear->id)
            ->firstOrFail();

        if ($register->isLocked()) {
            return back()->with('error', 'This register has been locked by FDE.');
        }

        $action = $request->input('action', 'save');

        $this->upsertEntries($register, $request->input('entries', []));

        if ($action === 'submit') {
            $register->update([
                'status'       => 'submitted',
                'submitted_by' => $user->id,
                'submitted_at' => now(),
            ]);
            return redirect()->route('hoi.staff-strength.index')
                ->with('success', 'Staff strength register submitted successfully.');
        }

        $register->update(['status' => 'draft']);
        return redirect()->route('hoi.staff-strength.index')
            ->with('success', 'Staff strength register saved as draft.');
    }

    private function upsertEntries(StaffStrengthRegister $register, array $entries): void
    {
        foreach ($entries as $postTypeId => $data) {
            StaffStrengthEntry::updateOrCreate(
                [
                    'register_id'  => $register->id,
                    'post_type_id' => (int) $postTypeId,
                ],
                [
                    'sanctioned_posts'  => (int) ($data['sanctioned_posts']  ?? 0),
                    'filled_posts'      => (int) ($data['filled_posts']      ?? 0),
                    'sacked_employees'  => (int) ($data['sacked_employees']  ?? 0),
                    'daily_wagers_in'   => (int) ($data['daily_wagers_in']   ?? 0),
                    'daily_wagers_out'  => (int) ($data['daily_wagers_out']  ?? 0),
                    'study_leave'       => (int) ($data['study_leave']       ?? 0),
                    'deputationist_in'  => (int) ($data['deputationist_in']  ?? 0),
                    'deputationist_out' => (int) ($data['deputationist_out'] ?? 0),
                    'temporary_in'      => (int) ($data['temporary_in']      ?? 0),
                    'temporary_out'     => (int) ($data['temporary_out']     ?? 0),
                    'number_of_posts'   => (int) ($data['number_of_posts']   ?? 0),
                ]
            );
        }
    }
}

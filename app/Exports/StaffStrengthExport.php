<?php

namespace App\Exports;

use App\Models\StaffStrengthRegister;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class StaffStrengthExport implements FromView
{
    public function __construct(
        private StaffStrengthRegister $register
    ) {}

    public function view(): View
    {
        $register = $this->register->load([
            'institution.sector',
            'academicYear',
            'submittedBy',
            'lockedBy',
            'entries.postType',
        ]);

        $teachingEntries = $register->entries
            ->filter(fn($e) => $e->postType->section === 'teaching')
            ->sortBy('postType.sort_order');

        $programEntries = $register->entries
            ->filter(fn($e) => $e->postType->section === 'program')
            ->sortBy('postType.sort_order');

        return view('fde.staff-strength.excel', compact(
            'register',
            'teachingEntries',
            'programEntries',
        ));
    }
}

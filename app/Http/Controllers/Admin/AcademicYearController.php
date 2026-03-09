<?php
// SAVE AS: app/Http/Controllers/Admin/AcademicYearController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicYear;

class AcademicYearController extends Controller
{
    public function index()
    {
        $years = AcademicYear::orderByDesc('start_date')->get();
        return view('admin.academic_years.index', compact('years'));
    }

    public function create()
    {
        return view('admin.academic_years.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:20|unique:academic_years,name',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after:start_date',
            'admission_start'   => 'nullable|date',
            'admission_end'     => 'nullable|date|after_or_equal:admission_start',
            'daily_cutoff_time' => 'required|date_format:H:i',
            'is_active'         => 'boolean',
        ]);

        $data['daily_cutoff_time'] = $data['daily_cutoff_time'] . ':00';

        DB::transaction(function () use ($data) {
            // Only one active year at a time
            if (!empty($data['is_active'])) {
                AcademicYear::where('is_active', true)->update(['is_active' => false]);
            }
            AcademicYear::create($data);
        });

        return redirect()->route('admin.academic-years.index')
            ->with('success', "Academic year '{$data['name']}' created successfully.");
    }

    public function edit(AcademicYear $academicYear)
    {
        return view('admin.academic_years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:20|unique:academic_years,name,' . $academicYear->id,
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after:start_date',
            'admission_start'   => 'nullable|date',
            'admission_end'     => 'nullable|date|after_or_equal:admission_start',
            'daily_cutoff_time' => 'required|date_format:H:i',
            'is_active'         => 'boolean',
        ]);

        $data['daily_cutoff_time'] = $data['daily_cutoff_time'] . ':00';

        DB::transaction(function () use ($data, $academicYear) {
            if (!empty($data['is_active'])) {
                AcademicYear::where('is_active', true)
                    ->where('id', '!=', $academicYear->id)
                    ->update(['is_active' => false]);
            }
            $academicYear->update($data);
        });

        return redirect()->route('admin.academic-years.index')
            ->with('success', "Academic year '{$academicYear->name}' updated.");
    }

    // Quick toggle — set this year as active, deactivate all others
    public function setActive(AcademicYear $academicYear)
    {
        DB::transaction(function () use ($academicYear) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
            $academicYear->update(['is_active' => true]);
        });

        return redirect()->route('admin.academic-years.index')
            ->with('success', "'{$academicYear->name}' is now the active academic year.");
    }
}

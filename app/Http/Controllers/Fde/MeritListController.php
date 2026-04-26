<?php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\InstitutionMeritList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MeritListController extends Controller
{
    public function index()
    {
        // All institutions with their merit lists (eager-loaded, sorted latest first)
        $institutions = Institution::where('is_active', true)
            ->with(['meritLists' => fn($q) => $q->latest(), 'sector'])
            ->whereHas('meritLists')
            ->orderBy('name')
            ->get();

        // All institutions (for the upload form dropdown)
        $allInstitutions = Institution::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $totalFiles = InstitutionMeritList::count();

        return view('fde.merit_lists.index', compact(
            'institutions',
            'allInstitutions',
            'totalFiles'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'institution_id' => 'required|exists:institutions,id',
            'title'          => 'nullable|string|max:200',
            'files'          => 'required|array|min:1|max:10',
            'files.*'        => 'file|mimes:pdf,xlsx,xls,csv|max:10240',
        ], [
            'files.required' => 'Please select at least one file.',
            'files.*.mimes'  => 'Each file must be PDF, Excel, or CSV.',
            'files.*.max'    => 'Each file must be 10 MB or less.',
        ]);

        $institutionId = $request->institution_id;
        $title         = $request->filled('title') ? $request->title : null;
        $directory     = "portal/merit-lists/{$institutionId}";
        $uploaded      = 0;

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $ext          = $file->getClientOriginalExtension();
            $slug         = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
            $timestamp    = now()->format('Ymd-His') . '-' . $uploaded;
            $filename     = "{$slug}-{$timestamp}.{$ext}";

            $path = $file->storeAs($directory, $filename, 'public');

            InstitutionMeritList::create([
                'institution_id' => $institutionId,
                'title'          => $title,
                'file_path'      => $path,
                'original_name'  => $originalName,
                'file_size'      => $file->getSize(),
            ]);

            $uploaded++;
        }

        return redirect()->route('fde.merit-lists.index')
            ->with('success', "{$uploaded} file(s) uploaded successfully.");
    }

    public function destroy(InstitutionMeritList $meritList)
    {
        Storage::disk('public')->delete($meritList->file_path);
        $meritList->delete();

        return redirect()->route('fde.merit-lists.index')
            ->with('success', 'File removed successfully.');
    }
}

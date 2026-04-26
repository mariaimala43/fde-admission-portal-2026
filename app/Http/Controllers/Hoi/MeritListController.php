<?php

namespace App\Http\Controllers\Hoi;

use App\Http\Controllers\Controller;
use App\Models\InstitutionMeritList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MeritListController extends Controller
{
    public function index()
    {
        $institution = Auth::user()->institution;

        if (! $institution) {
            return redirect()->route('hoi.profile.setup');
        }

        $meritLists = InstitutionMeritList::where('institution_id', $institution->id)
            ->latest()
            ->get();

        return view('hoi.merit_lists.index', compact('institution', 'meritLists'));
    }

    public function store(Request $request)
    {
        $institution = Auth::user()->institution;
        abort_if(! $institution, 403, 'No institution assigned.');

        $request->validate([
            'title' => 'nullable|string|max:200',
            'file'  => 'required|file|mimes:pdf,xlsx,xls,csv|max:10240',
        ]);

        $file         = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $ext          = $file->getClientOriginalExtension();
        $slug         = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $timestamp    = now()->format('Ymd-His');
        $filename     = "{$slug}-{$timestamp}.{$ext}";
        $directory    = "portal/merit-lists/{$institution->id}";

        $path = $file->storeAs($directory, $filename, 'public');

        InstitutionMeritList::create([
            'institution_id' => $institution->id,
            'title'          => $request->filled('title') ? $request->title : null,
            'file_path'      => $path,
            'original_name'  => $originalName,
            'file_size'      => $file->getSize(),
        ]);

        return redirect()->route('hoi.merit-lists.index')
            ->with('success', 'File uploaded successfully.');
    }

    public function destroy(InstitutionMeritList $meritList)
    {
        $institution = Auth::user()->institution;
        abort_if(
            ! $institution || $meritList->institution_id !== $institution->id,
            403,
            'Unauthorized.'
        );

        Storage::disk('public')->delete($meritList->file_path);
        $meritList->delete();

        return redirect()->route('hoi.merit-lists.index')
            ->with('success', 'File removed successfully.');
    }
}

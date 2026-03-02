<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\SchoolsImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    // ── Show import page ───────────────────────────────────
    public function index()
    {
        return view('admin.import.index');
    }

    // ── Handle file upload and import ──────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new SchoolsImport();

        Excel::import($import, $request->file('file'));

        $message = "Import complete.
            Imported: {$import->imported},
            Skipped: {$import->skipped}";

        if (!empty($import->errors)) {
            return back()
                ->with('warning', $message)
                ->with('import_errors', $import->errors);
        }

        return back()->with('success', $message);
    }
}

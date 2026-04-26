<?php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use App\Models\UcControlRoom;
use App\Models\UnionCouncil;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class UcControlRoomController extends Controller
{
    // ── Index — searchable list of all 32 UC control rooms ─────────────────

    public function index(Request $request)
    {
        $search = $request->input('search');
        $org    = $request->input('org');

        $records = UcControlRoom::with('unionCouncil')
            ->when($search, fn ($q) =>
                $q->whereHas('unionCouncil', fn ($uc) =>
                    $uc->where('name', 'like', "%{$search}%")
                       ->orWhere('code', 'like', "%{$search}%")
                )
                ->orWhere('fde_school_name',       'like', "%{$search}%")
                ->orWhere('focal_person_name',      'like', "%{$search}%")
                ->orWhere('nchd_fo_name',           'like', "%{$search}%")
                ->orWhere('fde_focal_person_name',  'like', "%{$search}%")
            )
            ->when($org, fn ($q) =>
                $q->where('organization_name', 'like', "%{$org}%")
            )
            ->orderBy(
                UnionCouncil::select('code')
                    ->whereColumn('id', 'uc_control_rooms.uc_id')
                    ->limit(1)
            )
            ->get();

        // Distinct organisation list for filter dropdown
        $organizations = UcControlRoom::distinct()
            ->orderBy('organization_name')
            ->pluck('organization_name')
            ->filter()
            ->values();

        $totalUcs  = $records->count();
        $totalOrgs = $organizations->count();

        return view('fde.uc-control-rooms.index', compact(
            'records', 'organizations', 'totalUcs', 'totalOrgs', 'search', 'org'
        ));
    }

    // ── Show — detail card for a single UC control room ────────────────────

    public function show(UcControlRoom $ucControlRoom)
    {
        $ucControlRoom->load('unionCouncil.sector');

        return view('fde.uc-control-rooms.show', compact('ucControlRoom'));
    }

    // ── Export PDF — A4 landscape, all filtered records ─────────────────────

    public function exportPdf(Request $request)
    {
        $search = $request->input('search');
        $org    = $request->input('org');

        $records = UcControlRoom::with('unionCouncil')
            ->when($search, fn ($q) =>
                $q->whereHas('unionCouncil', fn ($uc) =>
                    $uc->where('name', 'like', "%{$search}%")
                       ->orWhere('code', 'like', "%{$search}%")
                )
                ->orWhere('fde_school_name',      'like', "%{$search}%")
                ->orWhere('focal_person_name',     'like', "%{$search}%")
                ->orWhere('nchd_fo_name',          'like', "%{$search}%")
                ->orWhere('fde_focal_person_name', 'like', "%{$search}%")
            )
            ->when($org, fn ($q) =>
                $q->where('organization_name', 'like', "%{$org}%")
            )
            ->orderBy(
                UnionCouncil::select('code')
                    ->whereColumn('id', 'uc_control_rooms.uc_id')
                    ->limit(1)
            )
            ->get();

        $generatedAt = now()->format('d M Y, h:i A');

        $pdf = Pdf::loadView('fde.uc-control-rooms.pdf', compact('records', 'generatedAt', 'search', 'org'))
                  ->setPaper('a4', 'landscape');

        $filename = 'uc-control-rooms-' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}

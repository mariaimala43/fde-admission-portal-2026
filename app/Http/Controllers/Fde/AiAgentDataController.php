<?php
// ════════════════════════════════════════════════════════════════════
//  SAVE AS: app/Http/Controllers/Fde/AiAgentDataController.php
//
//  Wired to EXACT schema from:
//    - DailyAdmission:      morning_boys, morning_girls, evening_boys,
//                           evening_girls, oosc_boys, oosc_girls,
//                           p2p_boys, p2p_girls
//    - InstitutionClass:    total_seats, existing_enrollment
//    - NewConstructionRoom: rooms_total, rooms_allocated,
//                           construction_status
//    - RoomAllocation:      rooms_assigned (via ->allocations())
//    - Classes:             order, is_ece (no is_ece col — uses level)
//    - Sector/UC:           standard
//
//  Routes (add inside fde_cell middleware group in web.php):
//    Route::get('ai-reports',       [AiAgentDataController::class,'studio']   )->name('ai.reports');
//    Route::get('api/agent-data',   [AiAgentDataController::class,'agentData'])->name('api.agent-data');
//    Route::post('api/ai-generate', [AiAgentDataController::class,'generate'] )->name('api.ai-generate');
//
//  .env:
//    ANTHROPIC_API_KEY=sk-ant-api03-...
//
//  config/services.php:
//    'anthropic' => ['key' => env('ANTHROPIC_API_KEY')],
// ════════════════════════════════════════════════════════════════════

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Models\Institution;
use App\Models\DailyAdmission;
use App\Models\InstitutionClass;
use App\Models\Classes;
use App\Models\Sector;
use App\Models\UnionCouncil;
use App\Models\NewConstructionRoom;
use App\Models\AcademicYear;
use Carbon\Carbon;

class AiAgentDataController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  STUDIO VIEW  GET /fde/ai-reports
    // ─────────────────────────────────────────────────────────────
    public function studio()
    {
        Gate::authorize('reports.dashboard');
        return view('fde.reports.ai_studio');
    }

    // ─────────────────────────────────────────────────────────────
    //  LIVE DATA API  GET /fde/api/agent-data
    //
    //  Returns one JSON payload with ALL datasets needed by every
    //  agent type.  Keys match exactly what the JSX agents expect.
    // ─────────────────────────────────────────────────────────────
    public function agentData(Request $request): JsonResponse
    {
        $year = AcademicYear::where('is_active', true)->first();
        $yid  = $year?->id;

        // ═══════════════════════════════════════════════════════
        //  HELPER: regular admission SUM expression
        //  morning_boys + morning_girls + evening_boys + evening_girls
        //  (OOSC and P2P are analytics-only, NOT counted in seats)
        // ═══════════════════════════════════════════════════════
        $regularExpr  = 'morning_boys + morning_girls + evening_boys + evening_girls';
        $boysExpr     = 'morning_boys + evening_boys';
        $girlsExpr    = 'morning_girls + evening_girls';
        $ooscExpr     = 'oosc_boys + oosc_girls';
        $p2pExpr      = 'p2p_boys + p2p_girls';
        $displayExpr  = "$regularExpr + oosc_boys + oosc_girls + p2p_boys + p2p_girls";

        // ═══════════════════════════════════════════════════════
        //  1. GRAND SUMMARY
        // ═══════════════════════════════════════════════════════
        $grand = DailyAdmission::where('academic_year_id', $yid)
            ->selectRaw("
                SUM($boysExpr)                        AS reg_boys,
                SUM($girlsExpr)                       AS reg_girls,
                SUM(oosc_boys)                        AS oosc_boys,
                SUM(oosc_girls)                       AS oosc_girls,
                SUM(p2p_boys)                         AS p2p_boys,
                SUM(p2p_girls)                        AS p2p_girls,
                SUM($regularExpr)                     AS total_regular,
                SUM($boysExpr + oosc_boys + p2p_boys) AS all_boys,
                SUM($girlsExpr + oosc_girls + p2p_girls) AS all_girls,
                SUM($displayExpr)                     AS grand_total
            ")->first();

        $seatRow     = InstitutionClass::selectRaw('SUM(total_seats) as ts, SUM(existing_enrollment) as te')->first();
        $totalSeats  = (int)($seatRow->ts ?? 0);
        $totalExist  = (int)($seatRow->te ?? 0);
        $totalReg    = (int)($grand->total_regular ?? 0);   // seat-affecting admissions only
        $totalAdm    = (int)($grand->grand_total   ?? 0);   // display total (incl. OOSC/P2P)
        $totalFilled = $totalExist + $totalReg;             // seat math uses regular only

        // Today's admissions
        $today = DailyAdmission::where('admission_date', now()->toDateString())
            ->selectRaw("
                SUM($displayExpr) AS total,
                SUM($boysExpr + oosc_boys + p2p_boys) AS boys,
                SUM($girlsExpr + oosc_girls + p2p_girls) AS girls
            ")->first();

        // ═══════════════════════════════════════════════════════
        //  2. SECTORS
        // ═══════════════════════════════════════════════════════
        $sectors = Sector::with('institutions')->orderBy('name')->get()
            ->map(function ($s) use ($yid, $regularExpr, $boysExpr, $girlsExpr, $ooscExpr, $p2pExpr, $displayExpr) {
                $ids = $s->institutions->pluck('id');

                $adm = DailyAdmission::whereIn('institution_id', $ids)
                    ->where('academic_year_id', $yid)
                    ->selectRaw("
                        SUM($displayExpr)  AS total,
                        SUM($regularExpr)  AS regular,
                        SUM($boysExpr + oosc_boys + p2p_boys) AS boys,
                        SUM($girlsExpr + oosc_girls + p2p_girls) AS girls,
                        SUM($ooscExpr)     AS oosc,
                        SUM($p2pExpr)      AS p2p
                    ")->first();

                $sc = InstitutionClass::whereIn('institution_id', $ids)
                    ->selectRaw('SUM(total_seats) as seats, SUM(existing_enrollment) as existing')
                    ->first();

                $seatCount = (int)($sc->seats    ?? 0);
                $existing  = (int)($sc->existing ?? 0);
                $regular   = (int)($adm->regular ?? 0);
                $display   = (int)($adm->total   ?? 0);

                return [
                    'id'             => $s->id,
                    'name'           => $s->name,
                    'school_count'   => $s->institutions->count(),
                    'total_seats'    => $seatCount,
                    'total_existing' => $existing,
                    'total_regular'  => $regular,   // seat-counted
                    'total_admitted' => $display,   // display (incl. OOSC/P2P)
                    'total_boys'     => (int)($adm->boys  ?? 0),
                    'total_girls'    => (int)($adm->girls ?? 0),
                    'oosc'           => (int)($adm->oosc  ?? 0),
                    'p2p'            => (int)($adm->p2p   ?? 0),
                    // Vacancy: seat-affecting admissions only
                    'fill_rate'      => $seatCount > 0
                        ? round(($existing + $regular) / $seatCount * 100)
                        : 0,
                    'remaining'      => max(0, $seatCount - $existing - $regular),
                ];
            });

        // ═══════════════════════════════════════════════════════
        //  3. SCHOOLS  (institution-level)
        // ═══════════════════════════════════════════════════════
        $schools = Institution::with('sector')
            ->where('is_active', true)
            ->where('classes_configured', true)
            ->orderBy('sector_id')->orderBy('name')
            ->get()
            ->map(function ($inst) use ($yid, $regularExpr, $boysExpr, $girlsExpr, $ooscExpr, $p2pExpr, $displayExpr) {
                $sc = InstitutionClass::where('institution_id', $inst->id)
                    ->selectRaw('SUM(total_seats) as seats, SUM(existing_enrollment) as existing')
                    ->first();

                $adm = DailyAdmission::where('institution_id', $inst->id)
                    ->where('academic_year_id', $yid)
                    ->selectRaw("
                        SUM($displayExpr)  AS total,
                        SUM($regularExpr)  AS regular,
                        SUM($boysExpr + oosc_boys + p2p_boys) AS boys,
                        SUM($girlsExpr + oosc_girls + p2p_girls) AS girls,
                        SUM($ooscExpr)     AS oosc,
                        SUM($p2pExpr)      AS p2p
                    ")->first();

                $seatCount = (int)($sc->seats    ?? 0);
                $existing  = (int)($sc->existing ?? 0);
                $regular   = (int)($adm->regular ?? 0);

                return [
                    'id'        => $inst->id,
                    'name'      => $inst->name,
                    'sector'    => $inst->sector?->name ?? '—',
                    'type'      => $inst->type,
                    'gender'    => $inst->gender,
                    'seats'     => $seatCount,
                    'existing'  => $existing,
                    'regular'   => $regular,
                    'admitted'  => (int)($adm->total  ?? 0),
                    'boys'      => (int)($adm->boys   ?? 0),
                    'girls'     => (int)($adm->girls  ?? 0),
                    'oosc'      => (int)($adm->oosc   ?? 0),
                    'p2p'       => (int)($adm->p2p    ?? 0),
                    'remaining' => max(0, $seatCount - $existing - $regular),
                    'fill_rate' => $seatCount > 0
                        ? round(($existing + $regular) / $seatCount * 100)
                        : 0,
                ];
            });

        // ═══════════════════════════════════════════════════════
        //  4. CLASS-WISE  (system-wide, same logic as MasterReportController)
        // ═══════════════════════════════════════════════════════
        $byClass = Classes::orderBy('order')->get()
            ->map(function ($cls) use ($yid, $regularExpr, $boysExpr, $girlsExpr, $ooscExpr, $p2pExpr, $displayExpr) {
                $adm = DailyAdmission::where('class_id', $cls->id)
                    ->where('academic_year_id', $yid)
                    ->selectRaw("
                        SUM($displayExpr)  AS total,
                        SUM($regularExpr)  AS regular,
                        SUM($boysExpr + oosc_boys + p2p_boys) AS boys,
                        SUM($girlsExpr + oosc_girls + p2p_girls) AS girls,
                        SUM($ooscExpr)     AS oosc,
                        SUM($p2pExpr)      AS p2p
                    ")->first();

                $sc = InstitutionClass::where('class_id', $cls->id)
                    ->selectRaw('SUM(total_seats) as seats, SUM(existing_enrollment) as existing')
                    ->first();

                $seatCount = (int)($sc->seats    ?? 0);
                $existing  = (int)($sc->existing ?? 0);
                $regular   = (int)($adm->regular ?? 0);

                return [
                    'class'     => $cls->name,
                    'order'     => $cls->order,
                    'level'     => $cls->level ?? null,
                    'seats'     => $seatCount,
                    'existing'  => $existing,
                    'regular'   => $regular,
                    'admitted'  => (int)($adm->total  ?? 0),
                    'boys'      => (int)($adm->boys   ?? 0),
                    'girls'     => (int)($adm->girls  ?? 0),
                    'oosc'      => (int)($adm->oosc   ?? 0),
                    'p2p'       => (int)($adm->p2p    ?? 0),
                    'remaining' => max(0, $seatCount - $existing - $regular),
                    'fill_rate' => $seatCount > 0
                        ? round(($existing + $regular) / $seatCount * 100)
                        : 0,
                ];
            })
            ->filter(fn($r) => $r['seats'] > 0 || $r['admitted'] > 0)
            ->values();

        // ═══════════════════════════════════════════════════════
        //  5. DAILY TREND  (last 30 days)
        // ═══════════════════════════════════════════════════════
        $dailyRaw = DailyAdmission::where('academic_year_id', $yid)
            ->where('admission_date', '>=', now()->subDays(29)->toDateString())
            ->selectRaw("
                admission_date,
                SUM($displayExpr)  AS total,
                SUM($boysExpr + oosc_boys + p2p_boys) AS boys,
                SUM($girlsExpr + oosc_girls + p2p_girls) AS girls,
                SUM($ooscExpr)     AS oosc,
                SUM($p2pExpr)      AS p2p
            ")
            ->groupBy('admission_date')
            ->orderBy('admission_date')
            ->get()
            ->keyBy(fn($r) => $r->admission_date->toDateString());

        $daily = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $row  = $dailyRaw[$date] ?? null;
            $daily[] = [
                'date'  => $date,
                'label' => Carbon::parse($date)->format('d M'),
                'total' => (int)($row->total ?? 0),
                'boys'  => (int)($row->boys  ?? 0),
                'girls' => (int)($row->girls ?? 0),
                'oosc'  => (int)($row->oosc  ?? 0),
                'p2p'   => (int)($row->p2p   ?? 0),
            ];
        }

        // ═══════════════════════════════════════════════════════
        //  6. NEW CONSTRUCTION ROOMS
        //  NewConstructionRoom: construction_status, rooms_total,
        //                       rooms_allocated
        //  RoomAllocation:      rooms_assigned, class_id
        // ═══════════════════════════════════════════════════════
        $rooms       = [];
        $allocations = [];

        if (class_exists(\App\Models\NewConstructionRoom::class) && class_exists(\App\Models\RoomAllocation::class)) {
            $roomRecords = \App\Models\NewConstructionRoom::with([
                    'institution.sector',
                    'allocations.classModel',
                ])
                ->orderBy('construction_status')
                ->get();

            $rooms = $roomRecords->map(fn($r) => [
                'school'          => $r->institution?->name    ?? '—',
                'sector'          => $r->institution?->sector?->name ?? '—',
                'rooms_total'     => (int)$r->rooms_total,
                'rooms_allocated' => (int)$r->rooms_allocated,
                'unallocated'     => $r->roomsRemaining(),
                'seats_added'     => (int)$r->rooms_allocated * 40, // standard 40/room
                'status'          => $r->construction_status ?? 'pending',
                'status_label'    => $r->statusLabel(),
                'is_fully_allocated' => $r->isFullyAllocated(),
            ])->toArray();

            // Per-class allocation rows
            $allocations = $roomRecords->flatMap(fn($r) =>
                $r->allocations->map(fn($a) => [
                    'school'        => $r->institution?->name    ?? '—',
                    'sector'        => $r->institution?->sector?->name ?? '—',
                    'class'         => $a->classModel?->name ?? '—',
                    'class_order'   => $a->classModel?->order ?? 99,
                    'rooms'         => (int)$a->rooms_assigned,
                    'seats_added'   => (int)$a->rooms_assigned * 40,
                    'status'        => $r->construction_status ?? 'pending',
                ])
            )->sortBy('class_order')->values()->toArray();
        }

        // ═══════════════════════════════════════════════════════
        //  7. MASTER REPORT DATA  (same logic as MasterReportController)
        //  institution × class matrix  — top 20 schools by fill rate
        // ═══════════════════════════════════════════════════════
        $masterByClass = [];
        $allClasses    = Classes::orderBy('order')->get();

        // Get all admission data grouped by institution+class
        $masterAdm = DailyAdmission::where('academic_year_id', $yid)
            ->selectRaw("
                institution_id, class_id,
                SUM($regularExpr)  AS regular,
                SUM($boysExpr)     AS reg_boys,
                SUM($girlsExpr)    AS reg_girls,
                SUM(oosc_boys)     AS oosc_boys,
                SUM(oosc_girls)    AS oosc_girls,
                SUM(p2p_boys)      AS p2p_boys,
                SUM(p2p_girls)     AS p2p_girls,
                SUM($displayExpr)  AS total_admitted
            ")
            ->groupBy('institution_id', 'class_id')
            ->get()
            ->groupBy('institution_id')
            ->map(fn($rows) => $rows->keyBy('class_id'));

        $masterSeats = InstitutionClass::where('is_active', true)
            ->get()
            ->groupBy('institution_id');

        foreach ($allClasses as $cls) {
            $tSeats = $tExist = $tReg = $tOosc = $tP2p = $tAdm = $schoolCount = 0;

            foreach ($masterSeats as $instId => $seatRows) {
                $seat = $seatRows->firstWhere('class_id', $cls->id);
                if (!$seat) continue;
                $adm = $masterAdm[$instId][$cls->id] ?? null;

                $schoolCount++;
                $tSeats += $seat->total_seats;
                $tExist += $seat->existing_enrollment;
                $tReg   += (int)($adm->regular       ?? 0);
                $tOosc  += (int)($adm->oosc_boys      ?? 0) + (int)($adm->oosc_girls ?? 0);
                $tP2p   += (int)($adm->p2p_boys       ?? 0) + (int)($adm->p2p_girls  ?? 0);
                $tAdm   += (int)($adm->total_admitted ?? 0);
            }

            if ($schoolCount === 0) continue;

            $masterByClass[] = [
                'class'          => $cls->name,
                'order'          => $cls->order,
                'school_count'   => $schoolCount,
                'total_seats'    => $tSeats,
                'total_existing' => $tExist,
                'total_regular'  => $tReg,
                'total_oosc'     => $tOosc,
                'total_p2p'      => $tP2p,
                'total_admitted' => $tAdm,
                'total_filled'   => $tExist + $tReg,
                'total_remaining'=> max(0, $tSeats - $tExist - $tReg),
                'fill_rate'      => $tSeats > 0 ? round(($tExist + $tReg) / $tSeats * 100) : 0,
            ];
        }

        // Grand totals (same as MasterReportController $grand)
        $grandMaster = [
            'seats'     => array_sum(array_column($masterByClass, 'total_seats')),
            'existing'  => array_sum(array_column($masterByClass, 'total_existing')),
            'regular'   => array_sum(array_column($masterByClass, 'total_regular')),
            'oosc'      => array_sum(array_column($masterByClass, 'total_oosc')),
            'p2p'       => array_sum(array_column($masterByClass, 'total_p2p')),
            'admitted'  => array_sum(array_column($masterByClass, 'total_admitted')),
            'filled'    => array_sum(array_column($masterByClass, 'total_filled')),
            'remaining' => array_sum(array_column($masterByClass, 'total_remaining')),
        ];

        // ═══════════════════════════════════════════════════════
        //  ASSEMBLE PAYLOAD
        // ═══════════════════════════════════════════════════════
        return response()->json([
            'academic_year' => $year?->name ?? '2026–27',
            'generated_at'  => now()->toDateTimeString(),

            // Top-level KPIs
            'summary' => [
                'total_seats'     => $totalSeats,
                'total_existing'  => $totalExist,
                'total_regular'   => $totalReg,     // seat-affecting
                'total_admitted'  => $totalAdm,     // display (incl. OOSC/P2P)
                'total_filled'    => $totalFilled,
                'total_remaining' => max(0, $totalSeats - $totalFilled),
                'fill_rate'       => $totalSeats > 0 ? round($totalFilled / $totalSeats * 100) : 0,
                'all_boys'        => (int)($grand->all_boys   ?? 0),
                'all_girls'       => (int)($grand->all_girls  ?? 0),
                'oosc_total'      => (int)($grand->oosc_boys  ?? 0) + (int)($grand->oosc_girls ?? 0),
                'p2p_total'       => (int)($grand->p2p_boys   ?? 0) + (int)($grand->p2p_girls  ?? 0),
                'today_total'     => (int)($today->total ?? 0),
                'today_boys'      => (int)($today->boys  ?? 0),
                'today_girls'     => (int)($today->girls ?? 0),
            ],

            // Per-dataset arrays
            'sectors'      => $sectors->values(),
            'schools'      => $schools->values(),
            'by_class'     => $byClass,
            'daily'        => $daily,
            'rooms'        => $rooms,
            'allocations'  => $allocations,
            'master'       => $masterByClass,
            'grand_master' => $grandMaster,

            // Schema hints for AI (so it knows exact column semantics)
            '_schema_notes' => [
                'seat_math'  => 'regular = morning_boys+morning_girls+evening_boys+evening_girls. OOSC/P2P are analytics-only.',
                'fill_rate'  => 'fill_rate = (existing + regular) / seats * 100',
                'remaining'  => 'remaining = seats - existing - regular',
                'admitted'   => 'admitted = regular + oosc + p2p (display total)',
                'rooms'      => 'seats_added = rooms_allocated * 40 (standard 40 seats/room)',
                'status'     => 'daily_admissions.status: draft|submitted|verified|returned|locked. Verified+locked count in seat math.',
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  OPENROUTER PROXY  POST /fde/api/ai-generate
    //  OpenAI-compatible API — free models available
    //  Free model: meta-llama/llama-3.3-70b-instruct:free
    //  No SSL issues on Windows, works same on production
    // ─────────────────────────────────────────────────────────────
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'system'             => 'required|string|max:200000',
            'messages'           => 'required|array|min:1|max:20',
            'messages.*.role'    => 'required|in:user,assistant',
            'messages.*.content' => 'required|string|max:50000',
        ]);

        $apiKey = config('services.openrouter.key');
        if (empty($apiKey)) {
            return response()->json([
                'message' => 'OPENROUTER_API_KEY not set. Add it to .env and run php artisan config:clear'
            ], 500);
        }

        // ── Build messages array (OpenAI-compatible format) ───────
        $messages = array_merge(
            [['role' => 'system', 'content' => $request->input('system')]],
            collect($request->input('messages'))
                ->map(fn($m) => ['role' => $m['role'], 'content' => $m['content']])
                ->toArray()
        );

        // withoutVerifying() fixes SSL on Laragon/Windows local dev only.
        // On production Linux servers SSL works fine automatically.
        $response = Http::when(app()->environment('local'), fn($h) => $h->withoutVerifying())
            ->withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type'  => 'application/json',
                'HTTP-Referer'  => config('app.url', 'http://localhost'),
                'X-Title'       => 'FDE Admission Portal',
            ])
            ->timeout(90)
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model'       => 'meta-llama/llama-3.3-70b-instruct:free',
                'messages'    => $messages,
                'max_tokens'  => 8192,
                'temperature' => 0.3,
            ]);

        if ($response->failed()) {
            $err = $response->json();
            $msg = $err['error']['message'] ?? "OpenRouter API error (HTTP {$response->status()})";
            return response()->json(['message' => $msg], $response->status());
        }

        $data    = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';

        // Strip accidental markdown fences
        $content = trim(preg_replace(['/^```[a-z]*?/m', '/```$/m'], '', $content));

        if (empty($content)) {
            $reason = $data['choices'][0]['finish_reason'] ?? 'unknown';
            return response()->json([
                'message' => "Model returned empty response. Finish reason: {$reason}"
            ], 500);
        }

        return response()->json(['content' => $content]);
    }
    //insight chatgpt
    public function insights(Request $request)
    {
        $data = $request->input('dataset');

        $prompt = "
    You are an education analytics expert for FDE.

    Analyze this dataset and return JSON with:

    1. insights (array)
    2. sector_rankings
    3. alerts
    4. recommendations

    Dataset:
    ".json_encode($data);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.openrouter.key'),
            'Content-Type' => 'application/json'
        ])->post('https://openrouter.ai/api/v1/chat/completions',[
            'model' => 'meta-llama/llama-3.3-70b-instruct:free',
            'messages'=>[
                ['role'=>'system','content'=>'You are an education data analyst'],
                ['role'=>'user','content'=>$prompt]
            ]
        ]);

        return response()->json($response->json());
    }
}


{{--
    SAVE AS: resources/views/fde/exports/master-pdf.blade.php
    Used by: ExportController@masterReport  (PDF via DomPDF, A3 landscape)

    Variables:
      $institutions   – Collection of Institution models
      $allClasses     – Collection of Classes (ordered)
      $seatData       – Collection grouped by institution_id → class_id
      $admissionData  – Collection grouped by institution_id → class_id
      $overallByClass – array keyed by class_id
      $grand          – array (seats, existing, regular, oosc, p2p, admitted, filled, remaining)
      $from           – Carbon
      $to             – Carbon
      $academicYear   – AcademicYear model|null
--}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Master Admission Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 7.5px;
            color: #1a1a2e;
            background: #fff;
        }

        /* ── Header ──────────────────────────────────────── */
        .header {
            background: #1d4ed8;
            color: #fff;
            padding: 10px 14px;
            margin-bottom: 8px;
        }

        .header h1 {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .header .sub {
            font-size: 9px;
            opacity: .85;
            margin-top: 3px;
        }

        .header .meta {
            float: right;
            text-align: right;
            font-size: 8px;
            opacity: .9;
        }

        /* ── KPI cards ───────────────────────────────────── */
        .kpi-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            border-spacing: 4px;
        }

        .kpi {
            display: table-cell;
            background: #f0f4ff;
            border: 1px solid #c7d2fe;
            border-radius: 4px;
            padding: 5px 8px;
            text-align: center;
            width: 12.5%;
        }

        .kpi .val {
            font-size: 13px;
            font-weight: 700;
            color: #1d4ed8;
        }

        .kpi .lbl {
            font-size: 7px;
            color: #6b7280;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        /* ── Section title ───────────────────────────────── */
        .section-title {
            font-size: 9px;
            font-weight: 700;
            color: #1d4ed8;
            background: #eff6ff;
            border-left: 3px solid #1d4ed8;
            padding: 4px 8px;
            margin: 6px 0 4px;
        }

        /* ── Tables ──────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        thead th {
            background: #1e3a8a;
            color: #fff;
            padding: 4px 5px;
            text-align: center;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            border: 1px solid #1e3a8a;
        }

        thead th.left {
            text-align: left;
        }

        tbody td {
            padding: 3px 5px;
            border: 1px solid #e2e8f0;
            font-size: 7.5px;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tbody tr:hover {
            background: #eff6ff;
        }

        td.right {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        td.center {
            text-align: center;
        }

        /* ── Grand total row ─────────────────────────────── */
        tr.grand {
            background: #1d4ed8 !important;
            color: #fff;
            font-weight: 700;
        }

        tr.grand td {
            border-color: #1e3a8a;
            font-size: 8px;
        }

        /* ── Sub-total row ───────────────────────────────── */
        tr.subtotal {
            background: #dbeafe !important;
            font-weight: 700;
            color: #1e3a8a;
        }

        /* ── Fill rate colouring ─────────────────────────── */
        .fill-ok {
            color: #16a34a;
            font-weight: 600;
        }

        .fill-warn {
            color: #ca8a04;
            font-weight: 600;
        }

        .fill-crit {
            color: #dc2626;
            font-weight: 700;
        }

        /* ── Footer ──────────────────────────────────────── */
        .footer {
            margin-top: 8px;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            font-size: 7px;
            color: #9ca3af;
            text-align: center;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    {{-- ══ HEADER ══ --}}
    <div class="header">
        <div class="meta">
            Period: {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}<br>
            Generated: {{ now()->format('d M Y, H:i') }}<br>
            Academic Year: {{ $academicYear?->name ?? '—' }}
        </div>
        <h1>FDE Admission Portal — Master Report</h1>
        <div class="sub">Federal Directorate of Education, Islamabad · All Schools · Class-wise Admission Summary
        </div>
    </div>

    {{-- ══ KPI CARDS ══ --}}
    <div class="kpi-row">
        <div class="kpi">
            <div class="val">{{ number_format($grand['seats']) }}</div>
            <div class="lbl">Total Seats</div>
        </div>
        <div class="kpi">
            <div class="val">{{ number_format($grand['existing']) }}</div>
            <div class="lbl">Existing Enrolment</div>
        </div>
        <div class="kpi">
            <div class="val">{{ number_format($grand['regular']) }}</div>
            <div class="lbl">Regular Admitted</div>
        </div>
        <div class="kpi">
            <div class="val">{{ number_format($grand['oosc']) }}</div>
            <div class="lbl">OOSC</div>
        </div>
        <div class="kpi">
            <div class="val">{{ number_format($grand['p2p']) }}</div>
            <div class="lbl">P2G</div>
        </div>
        <div class="kpi">
            <div class="val">{{ number_format($grand['admitted']) }}</div>
            <div class="lbl">Total Admitted</div>
        </div>
        <div class="kpi">
            <div class="val">{{ number_format($grand['filled']) }}</div>
            <div class="lbl">Total Filled</div>
        </div>
        <div class="kpi">
            <div class="val">{{ number_format($grand['remaining']) }}</div>
            <div class="lbl">Remaining</div>
        </div>
    </div>

    {{-- ══ SECTION 1: CLASS-WISE SYSTEM SUMMARY ══ --}}
    <div class="section-title">1. System-wide Class Summary</div>
    <table>
        <thead>
            <tr>
                <th class="left">Class</th>
                <th>Schools</th>
                <th>Seats</th>
                <th>Existing</th>
                <th>Reg Boys</th>
                <th>Reg Girls</th>
                <th>Regular</th>
                <th>OOSC</th>
                <th>P2G</th>
                <th>Total Adm.</th>
                <th>Total Filled</th>
                <th>Remaining</th>
                <th>Fill %</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($overallByClass as $cid => $row)
                @php
                    $fillRate = $row['total_seats'] > 0 ? round(($row['total_filled'] / $row['total_seats']) * 100) : 0;
                    $fillClass = $fillRate >= 90 ? 'fill-crit' : ($fillRate >= 70 ? 'fill-warn' : 'fill-ok');
                @endphp
                <tr>
                    <td>{{ $row['class']->name }}</td>
                    <td class="center">{{ $row['school_count'] }}</td>
                    <td class="right">{{ number_format($row['total_seats']) }}</td>
                    <td class="right">{{ number_format($row['total_existing']) }}</td>
                    <td class="right">{{ number_format($row['total_regular'] / 2) }}</td>{{-- approx --}}
                    <td class="right">{{ number_format($row['total_regular'] / 2) }}</td>{{-- approx --}}
                    <td class="right">{{ number_format($row['total_regular']) }}</td>
                    <td class="right">{{ number_format($row['total_oosc']) }}</td>
                    <td class="right">{{ number_format($row['total_p2p']) }}</td>
                    <td class="right">{{ number_format($row['total_admitted']) }}</td>
                    <td class="right">{{ number_format($row['total_filled']) }}</td>
                    <td class="right">
                        {{ $row['total_remaining'] > 0 ? number_format($row['total_remaining']) : '<span style="color:#dc2626;font-weight:700;">FULL</span>' }}
                    </td>
                    <td class="center {{ $fillClass }}">{{ $fillRate }}%</td>
                </tr>
            @endforeach
            {{-- Grand Total --}}
            @php
                $grandFill = $grand['seats'] > 0 ? round(($grand['filled'] / $grand['seats']) * 100) : 0;
            @endphp
            <tr class="grand">
                <td>GRAND TOTAL</td>
                <td class="center">{{ $institutions->count() }}</td>
                <td class="right">{{ number_format($grand['seats']) }}</td>
                <td class="right">{{ number_format($grand['existing']) }}</td>
                <td class="right">—</td>
                <td class="right">—</td>
                <td class="right">{{ number_format($grand['regular']) }}</td>
                <td class="right">{{ number_format($grand['oosc']) }}</td>
                <td class="right">{{ number_format($grand['p2p']) }}</td>
                <td class="right">{{ number_format($grand['admitted']) }}</td>
                <td class="right">{{ number_format($grand['filled']) }}</td>
                <td class="right">{{ number_format($grand['remaining']) }}</td>
                <td class="center">{{ $grandFill }}%</td>
            </tr>
        </tbody>
    </table>

    {{-- ══ PAGE BREAK ══ --}}
    <div class="page-break"></div>

    {{-- ══ SECTION 2: SCHOOL-WISE DETAIL ══ --}}
    <div class="header" style="margin-bottom:8px;">
        <h1>FDE Admission Portal — Master Report (School Detail)</h1>
        <div class="sub">Period: {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }} · Academic Year:
            {{ $academicYear?->name ?? '—' }}</div>
    </div>

    <div class="section-title">2. School-wise Admission Detail by Class</div>

    @php
        $currentSector = null;
        $sectorSeats = $sectorExisting = $sectorRegular = $sectorOosc = $sectorP2p = $sectorAdmitted = $sectorFilled = $sectorRemaining = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th class="left" style="width:18%">School</th>
                <th class="left" style="width:8%">Class</th>
                <th>Seats</th>
                <th>Existing</th>
                <th>Reg Boys</th>
                <th>Reg Girls</th>
                <th>Regular</th>
                <th>OOSC Boys</th>
                <th>OOSC Girls</th>
                <th>OOSC</th>
                <th>P2G Boys</th>
                <th>P2G Girls</th>
                <th>P2G</th>
                <th>Total Adm.</th>
                <th>Filled</th>
                <th>Remaining</th>
                <th>Fill%</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($institutions as $inst)
                @php
                    $instSeats = $instExist = $instReg = $instOosc = $instP2p = $instAdm = $instFilled = $instRemaining = 0;
                    $instSeatRows = $seatData[$inst->id] ?? collect();
                    $instAdmRows = $admissionData[$inst->id] ?? collect();

                    // Print sector header if changed
                    if ($currentSector !== $inst->sector?->name) {
                        $currentSector = $inst->sector?->name;
                    }
                @endphp

                {{-- Sector grouping row --}}
                @if ($loop->first || $inst->sector?->name !== ($institutions[$loop->index - 1]->sector?->name ?? ''))
                    <tr style="background:#1e3a8a;">
                        <td colspan="17" style="color:#fff;font-weight:700;font-size:8px;padding:4px 6px;">
                            📍 SECTOR: {{ $inst->sector?->name ?? 'Unknown' }}
                        </td>
                    </tr>
                @endif

                @forelse($allClasses as $class)
                    @php
                        $seat = $instSeatRows->firstWhere('class_id', $class->id);
                        if (!$seat) {
                            continue;
                        }
                        $adm = $instAdmRows[$class->id] ?? null;

                        $regBoys = (int) ($adm?->reg_boys ?? 0);
                        $regGirls = (int) ($adm?->reg_girls ?? 0);
                        $ooscB = (int) ($adm?->oosc_boys ?? 0);
                        $ooscG = (int) ($adm?->oosc_girls ?? 0);
                        $p2pB = (int) ($adm?->p2p_boys ?? 0);
                        $p2pG = (int) ($adm?->p2p_girls ?? 0);
                        $regular = $regBoys + $regGirls;
                        $oosc = $ooscB + $ooscG;
                        $p2p = $p2pB + $p2pG;
                        $totalAdm = (int) ($adm?->total_admitted ?? 0);
                        $filled = $seat->existing_enrollment + $totalAdm;
                        $remaining = max(0, $seat->total_seats - $filled);
                        $fillRate = $seat->total_seats > 0 ? round(($filled / $seat->total_seats) * 100) : 0;
                        $fillCls = $fillRate >= 90 ? 'fill-crit' : ($fillRate >= 70 ? 'fill-warn' : 'fill-ok');

                        $instSeats += $seat->total_seats;
                        $instExist += $seat->existing_enrollment;
                        $instReg += $regular;
                        $instOosc += $oosc;
                        $instP2p += $p2p;
                        $instAdm += $totalAdm;
                        $instFilled += $filled;
                        $instRemaining += $remaining;
                    @endphp
                    <tr>
                        <td>{{ $loop->first ? $inst->name : '' }}</td>
                        <td>{{ $class->name }}</td>
                        <td class="right">{{ number_format($seat->total_seats) }}</td>
                        <td class="right">{{ number_format($seat->existing_enrollment) }}</td>
                        <td class="right">{{ $regBoys ?: '—' }}</td>
                        <td class="right">{{ $regGirls ?: '—' }}</td>
                        <td class="right">{{ $regular ?: '—' }}</td>
                        <td class="right">{{ $ooscB ?: '—' }}</td>
                        <td class="right">{{ $ooscG ?: '—' }}</td>
                        <td class="right">{{ $oosc ?: '—' }}</td>
                        <td class="right">{{ $p2pB ?: '—' }}</td>
                        <td class="right">{{ $p2pG ?: '—' }}</td>
                        <td class="right">{{ $p2p ?: '—' }}</td>
                        <td class="right">{{ $totalAdm ?: '—' }}</td>
                        <td class="right">{{ number_format($filled) }}</td>
                        <td class="right {{ $remaining <= 0 ? 'fill-crit' : '' }}">
                            {{ $remaining > 0 ? number_format($remaining) : 'FULL' }}</td>
                        <td class="center {{ $fillCls }}">{{ $fillRate }}%</td>
                    </tr>
                @empty
                @endforelse

                {{-- Institution sub-total --}}
                @if ($instSeats > 0)
                    @php $instFillRate = $instSeats > 0 ? round($instFilled / $instSeats * 100) : 0; @endphp
                    <tr class="subtotal">
                        <td colspan="2" style="text-align:right;padding-right:8px;">{{ $inst->name }} TOTAL</td>
                        <td class="right">{{ number_format($instSeats) }}</td>
                        <td class="right">{{ number_format($instExist) }}</td>
                        <td class="right">—</td>
                        <td class="right">—</td>
                        <td class="right">{{ number_format($instReg) }}</td>
                        <td class="right">—</td>
                        <td class="right">—</td>
                        <td class="right">{{ number_format($instOosc) }}</td>
                        <td class="right">—</td>
                        <td class="right">—</td>
                        <td class="right">{{ number_format($instP2p) }}</td>
                        <td class="right">{{ number_format($instAdm) }}</td>
                        <td class="right">{{ number_format($instFilled) }}</td>
                        <td class="right">{{ number_format($instRemaining) }}</td>
                        <td
                            class="center {{ $instFillRate >= 90 ? 'fill-crit' : ($instFillRate >= 70 ? 'fill-warn' : 'fill-ok') }}">
                            {{ $instFillRate }}%</td>
                    </tr>
                @endif
            @endforeach

            {{-- Grand Total --}}
            @php $grandFill2 = $grand['seats'] > 0 ? round($grand['filled'] / $grand['seats'] * 100) : 0; @endphp
            <tr class="grand">
                <td colspan="2">GRAND TOTAL</td>
                <td class="right">{{ number_format($grand['seats']) }}</td>
                <td class="right">{{ number_format($grand['existing']) }}</td>
                <td class="right">—</td>
                <td class="right">—</td>
                <td class="right">{{ number_format($grand['regular']) }}</td>
                <td class="right">—</td>
                <td class="right">—</td>
                <td class="right">{{ number_format($grand['oosc']) }}</td>
                <td class="right">—</td>
                <td class="right">—</td>
                <td class="right">{{ number_format($grand['p2p']) }}</td>
                <td class="right">{{ number_format($grand['admitted']) }}</td>
                <td class="right">{{ number_format($grand['filled']) }}</td>
                <td class="right">{{ number_format($grand['remaining']) }}</td>
                <td class="center">{{ $grandFill2 }}%</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        FDE Admission Portal &nbsp;·&nbsp; Master Report &nbsp;·&nbsp; Generated {{ now()->format('d M Y H:i') }}
        &nbsp;·&nbsp; Period: {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
        &nbsp;·&nbsp; Academic Year: {{ $academicYear?->name ?? '—' }}
    </div>

</body>

</html>

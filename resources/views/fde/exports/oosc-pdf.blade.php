{{--
    SAVE AS: resources/views/fde/exports/oosc-pdf.blade.php
    Used by: ExportController@ooscReport  (PDF via DomPDF, A4 landscape)

    Variables:
      $institutions – Collection of Institution models (with sector)
      $ooscData     – Collection keyed by institution_id
                      fields: oosc_boys, oosc_girls, oosc_total,
                              p2p_boys, p2p_girls, p2p_total
      $from         – Carbon
      $to           – Carbon
      $academicYear – AcademicYear model|null
--}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>OOSC & P2G Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 8px;
            color: #1a1a2e;
        }

        .header {
            background: #7c3aed;
            color: #fff;
            padding: 10px 14px;
            margin-bottom: 8px;
        }

        .header h1 {
            font-size: 13px;
            font-weight: 700;
        }

        .header .sub {
            font-size: 8.5px;
            opacity: .85;
            margin-top: 3px;
        }

        .header .meta {
            float: right;
            font-size: 8px;
            text-align: right;
            opacity: .9;
        }

        .kpi-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            border-spacing: 4px;
        }

        .kpi {
            display: table-cell;
            background: #f5f3ff;
            border: 1px solid #c4b5fd;
            border-radius: 4px;
            padding: 5px 8px;
            text-align: center;
        }

        .kpi .val {
            font-size: 13px;
            font-weight: 700;
            color: #7c3aed;
        }

        .kpi .lbl {
            font-size: 7px;
            color: #6b7280;
            margin-top: 2px;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        thead tr.group-hdr th {
            background: #4c1d95;
            color: #fff;
            padding: 3px 5px;
            font-size: 7.5px;
            font-weight: 700;
            text-align: center;
            border: 1px solid #4c1d95;
        }

        thead tr.col-hdr th {
            background: #6d28d9;
            color: #fff;
            padding: 3px 5px;
            font-size: 7px;
            text-transform: uppercase;
            border: 1px solid #4c1d95;
            text-align: center;
        }

        thead th.left {
            text-align: left;
        }

        tbody td {
            padding: 3px 5px;
            border: 1px solid #ede9fe;
            font-size: 8px;
        }

        tbody tr:nth-child(even) {
            background: #faf5ff;
        }

        td.right {
            text-align: right;
        }

        td.center {
            text-align: center;
        }

        tr.grand {
            background: #7c3aed !important;
            color: #fff;
            font-weight: 700;
        }

        tr.sector-hdr td {
            background: #ede9fe;
            font-weight: 700;
            color: #4c1d95;
            font-size: 8.5px;
            padding: 4px 6px;
        }

        .highlight {
            color: #7c3aed;
            font-weight: 700;
        }

        .zero {
            color: #d1d5db;
        }

        .footer {
            margin-top: 8px;
            border-top: 1px solid #ede9fe;
            padding-top: 4px;
            font-size: 7px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="meta">
            Period: {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}<br>
            Generated: {{ now()->format('d M Y, H:i') }}<br>
            Academic Year: {{ $academicYear?->name ?? '—' }}
        </div>
        <h1>FDE Admission Portal — OOSC &amp; P2G Report</h1>
        <div class="sub">Federal Directorate of Education · Out-of-School Children &amp; Private to Government Admission
            Tracking</div>
    </div>

    @php
        $totOoscBoys = $ooscData->sum('oosc_boys');
        $totOoscGirls = $ooscData->sum('oosc_girls');
        $totOosc = $ooscData->sum('oosc_total');
        $totP2pBoys = $ooscData->sum('p2p_boys');
        $totP2pGirls = $ooscData->sum('p2p_girls');
        $totP2p = $ooscData->sum('p2p_total');
        $totCombined = $totOosc + $totP2p;
        $schoolsWithOosc = $ooscData->where('oosc_total', '>', 0)->count();
        $schoolsWithP2p = $ooscData->where('p2p_total', '>', 0)->count();
    @endphp

    <div class="kpi-row">
        <div class="kpi">
            <div class="val">{{ $institutions->count() }}</div>
            <div class="lbl">Schools</div>
        </div>
        <div class="kpi">
            <div class="val">{{ number_format($totOoscBoys) }}</div>
            <div class="lbl">OOSC Boys</div>
        </div>
        <div class="kpi">
            <div class="val">{{ number_format($totOoscGirls) }}</div>
            <div class="lbl">OOSC Girls</div>
        </div>
        <div class="kpi">
            <div class="val highlight">{{ number_format($totOosc) }}</div>
            <div class="lbl">Total OOSC</div>
        </div>
        <div class="kpi">
            <div class="val">{{ number_format($totP2pBoys) }}</div>
            <div class="lbl">P2G Boys</div>
        </div>
        <div class="kpi">
            <div class="val">{{ number_format($totP2pGirls) }}</div>
            <div class="lbl">P2G Girls</div>
        </div>
        <div class="kpi">
            <div class="val highlight">{{ number_format($totP2p) }}</div>
            <div class="lbl">Total P2G</div>
        </div>
        <div class="kpi">
            <div class="val" style="color:#dc2626;">{{ number_format($totCombined) }}</div>
            <div class="lbl">Grand Total</div>
        </div>
    </div>

    <table>
        <thead>
            <tr class="group-hdr">
                <th class="left" rowspan="2" style="width:22%">School</th>
                <th class="left" rowspan="2" style="width:11%">Sector</th>
                <th colspan="3" style="background:#5b21b6;border-bottom:2px solid #7c3aed;">OOSC (Out-of-School
                    Children)</th>
                <th colspan="3" style="background:#1e3a8a;border-bottom:2px solid #3b82f6;">P2G (Private to Government)
                </th>
                <th rowspan="2" style="background:#1f2937;">Combined Total</th>
            </tr>
            <tr class="col-hdr">
                <th>Boys</th>
                <th>Girls</th>
                <th>Total</th>
                <th style="background:#1d4ed8;">Boys</th>
                <th style="background:#1d4ed8;">Girls</th>
                <th style="background:#1d4ed8;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $prevSector = null; @endphp
            @foreach ($institutions as $inst)
                @php
                    $row = $ooscData[$inst->id] ?? null;
                    $ooscB = (int) ($row?->oosc_boys ?? 0);
                    $ooscG = (int) ($row?->oosc_girls ?? 0);
                    $ooscT = (int) ($row?->oosc_total ?? 0);
                    $p2pB = (int) ($row?->p2p_boys ?? 0);
                    $p2pG = (int) ($row?->p2p_girls ?? 0);
                    $p2pT = (int) ($row?->p2p_total ?? 0);
                    $combined = $ooscT + $p2pT;
                @endphp

                @if ($inst->sector?->name !== $prevSector)
                    @php $prevSector = $inst->sector?->name; @endphp
                    <tr>
                        <td colspan="9" class="sector-hdr">📍 {{ $prevSector ?? 'Unknown Sector' }}</td>
                    </tr>
                @endif

                <tr>
                    <td>{{ $inst->name }}</td>
                    <td>{{ $inst->sector?->name ?? '—' }}</td>
                    <td class="right {{ $ooscB ? 'highlight' : 'zero' }}">{{ $ooscB ?: '—' }}</td>
                    <td class="right {{ $ooscG ? 'highlight' : 'zero' }}">{{ $ooscG ?: '—' }}</td>
                    <td class="right {{ $ooscT ? 'highlight' : 'zero' }}">{{ $ooscT ?: '—' }}</td>
                    <td class="right {{ $p2pB ? '' : 'zero' }}">{{ $p2pB ?: '—' }}</td>
                    <td class="right {{ $p2pG ? '' : 'zero' }}">{{ $p2pG ?: '—' }}</td>
                    <td class="right {{ $p2pT ? '' : 'zero' }}">{{ $p2pT ?: '—' }}</td>
                    <td class="right" style="font-weight:{{ $combined ? '700' : '400' }};">{{ $combined ?: '—' }}
                    </td>
                </tr>
            @endforeach

            <tr class="grand">
                <td colspan="2">GRAND TOTAL ({{ $institutions->count() }} schools · {{ $schoolsWithOosc }} with
                    OOSC · {{ $schoolsWithP2p }} with P2G)</td>
                <td class="right">{{ number_format($totOoscBoys) }}</td>
                <td class="right">{{ number_format($totOoscGirls) }}</td>
                <td class="right">{{ number_format($totOosc) }}</td>
                <td class="right">{{ number_format($totP2pBoys) }}</td>
                <td class="right">{{ number_format($totP2pGirls) }}</td>
                <td class="right">{{ number_format($totP2p) }}</td>
                <td class="right">{{ number_format($totCombined) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        FDE Admission Portal &nbsp;·&nbsp; OOSC &amp; P2G Report &nbsp;·&nbsp; Generated
        {{ now()->format('d M Y H:i') }}
        &nbsp;·&nbsp; Period: {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
        &nbsp;·&nbsp; Academic Year: {{ $academicYear?->name ?? '—' }}
    </div>
</body>

</html>

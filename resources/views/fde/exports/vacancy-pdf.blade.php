{{--
    SAVE AS: resources/views/fde/exports/vacancy-pdf.blade.php
    Used by: ExportController@vacancyReport  (PDF via DomPDF, A4 landscape)

    Variables:
      $institutions  – Collection of Institution models (with sector)
      $seatData      – Collection grouped by institution_id → InstitutionClass rows
      $admData       – Collection keyed by institution_id (total admitted)
      $academicYear  – AcademicYear model|null
--}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Vacancy Report</title>
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
            background: #065f46;
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
            background: #ecfdf5;
            border: 1px solid #6ee7b7;
            border-radius: 4px;
            padding: 5px 8px;
            text-align: center;
        }

        .kpi .val {
            font-size: 13px;
            font-weight: 700;
            color: #065f46;
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

        thead th {
            background: #064e3b;
            color: #fff;
            padding: 4px 5px;
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            border: 1px solid #064e3b;
            text-align: center;
        }

        thead th.left {
            text-align: left;
        }

        tbody td {
            padding: 3px 5px;
            border: 1px solid #d1fae5;
            font-size: 8px;
        }

        tbody tr:nth-child(even) {
            background: #f0fdf4;
        }

        td.right {
            text-align: right;
        }

        td.center {
            text-align: center;
        }

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

        tr.grand {
            background: #065f46 !important;
            color: #fff;
            font-weight: 700;
        }

        tr.sector-hdr {
            background: #d1fae5;
            font-weight: 700;
            color: #065f46;
            font-size: 8.5px;
        }

        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 8px;
            font-size: 6.5px;
            font-weight: 700;
        }

        .badge-full {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-ok {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge-warn {
            background: #fef9c3;
            color: #ca8a04;
        }

        .footer {
            margin-top: 8px;
            border-top: 1px solid #d1fae5;
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
            Generated: {{ now()->format('d M Y, H:i') }}<br>
            Academic Year: {{ $academicYear?->name ?? '—' }}
        </div>
        <h1>FDE Admission Portal — Vacancy Report</h1>
        <div class="sub">Federal Directorate of Education, Islamabad · Seat Capacity & Fill Rate Analysis</div>
    </div>

    @php
        $totalSeats = 0;
        $totalExist = 0;
        $totalAdm = 0;
        $totalFilled = 0;
        $fullCount = 0;

        foreach ($institutions as $inst) {
            $seats = ($seatData[$inst->id] ?? collect())->sum('total_seats');
            $exist = ($seatData[$inst->id] ?? collect())->sum('existing_enrollment');
            $adm = (int) ($admData[$inst->id]->total ?? 0);
            $filled = $exist + $adm;
            $rem = max(0, $seats - $filled);
            $totalSeats  += $seats;
            $totalExist  += $exist;
            $totalAdm    += $adm;
            $totalFilled += $filled;
            if ($rem <= 0 && $seats > 0) {
                $fullCount++;
            }
        }
        // Apply max(0,...) once on the aggregate — not per-school — to avoid
        // over-enrolled schools clamping to 0 instead of offsetting the total.
        $totalRemaining = max(0, $totalSeats - $totalFilled);
        $systemFill = $totalSeats > 0 ? round(($totalFilled / $totalSeats) * 100) : 0;
    @endphp

    <div class="kpi-row">
        <div class="kpi">
            <div class="val">{{ $institutions->count() }}</div>
            <div class="lbl">Schools</div>
        </div>
        <div class="kpi">
            <div class="val">{{ number_format($totalSeats) }}</div>
            <div class="lbl">Total Seats</div>
        </div>
        <div class="kpi">
            <div class="val">{{ number_format($totalExist) }}</div>
            <div class="lbl">Existing Enrolment</div>
        </div>
        <div class="kpi">
            <div class="val">{{ number_format($totalAdm) }}</div>
            <div class="lbl">New Admissions</div>
        </div>
        <div class="kpi">
            <div class="val">{{ number_format($totalFilled) }}</div>
            <div class="lbl">Total Filled</div>
        </div>
        <div class="kpi">
            <div class="val">{{ number_format($totalRemaining) }}</div>
            <div class="lbl">Remaining Seats</div>
        </div>
        <div class="kpi">
            <div class="val {{ $systemFill >= 90 ? 'fill-crit' : ($systemFill >= 70 ? 'fill-warn' : 'fill-ok') }}">
                {{ $systemFill }}%</div>
            <div class="lbl">System Fill Rate</div>
        </div>
        <div class="kpi">
            <div class="val" style="color:#dc2626;">{{ $fullCount }}</div>
            <div class="lbl">Schools Full</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="left" style="width:22%">School</th>
                <th class="left" style="width:12%">Sector</th>
                <th>Type</th>
                <th>Gender</th>
                <th>Total Seats</th>
                <th>Existing</th>
                <th>New Admissions</th>
                <th>Total Filled</th>
                <th>Remaining</th>
                <th>Fill Rate</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php $prevSector = null; @endphp
            @foreach ($institutions as $inst)
                @php
                    $seats = ($seatData[$inst->id] ?? collect())->sum('total_seats');
                    $exist = ($seatData[$inst->id] ?? collect())->sum('existing_enrollment');
                    $adm = (int) ($admData[$inst->id]->total ?? 0);
                    $filled = $exist + $adm;
                    $rem = max(0, $seats - $filled);
                    $fill = $seats > 0 ? round(($filled / $seats) * 100) : 0;
                    $fillCls = $fill >= 90 ? 'fill-crit' : ($fill >= 70 ? 'fill-warn' : 'fill-ok');
                @endphp

                @if ($inst->sector?->name !== $prevSector)
                    @php $prevSector = $inst->sector?->name; @endphp
                    <tr class="sector-hdr">
                        <td colspan="11">📍 {{ $prevSector ?? 'Unknown Sector' }}</td>
                    </tr>
                @endif

                <tr>
                    <td>{{ $inst->name }}</td>
                    <td>{{ $inst->sector?->name ?? '—' }}</td>
                    <td class="center">{{ ucfirst($inst->type ?? '—') }}</td>
                    <td class="center">{{ ucfirst($inst->gender ?? '—') }}</td>
                    <td class="right">{{ number_format($seats) }}</td>
                    <td class="right">{{ number_format($exist) }}</td>
                    <td class="right">{{ $adm ?: '—' }}</td>
                    <td class="right">{{ number_format($filled) }}</td>
                    <td class="right {{ $rem <= 0 ? 'fill-crit' : '' }}">{{ $rem > 0 ? number_format($rem) : 'FULL' }}
                    </td>
                    <td class="center {{ $fillCls }}">{{ $fill }}%</td>
                    <td class="center">
                        @if ($rem <= 0 && $seats > 0)
                            <span class="badge badge-full">FULL</span>
                        @elseif($fill >= 70)
                            <span class="badge badge-warn">NEAR FULL</span>
                        @else
                            <span class="badge badge-ok">OPEN</span>
                        @endif
                    </td>
                </tr>
            @endforeach

            <tr class="grand">
                <td colspan="4">GRAND TOTAL ({{ $institutions->count() }} schools)</td>
                <td class="right">{{ number_format($totalSeats) }}</td>
                <td class="right">{{ number_format($totalExist) }}</td>
                <td class="right">{{ number_format($totalAdm) }}</td>
                <td class="right">{{ number_format($totalFilled) }}</td>
                <td class="right">{{ number_format($totalRemaining) }}</td>
                <td class="center">{{ $systemFill }}%</td>
                <td class="center">{{ $fullCount }} Full</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        FDE Admission Portal &nbsp;·&nbsp; Vacancy Report &nbsp;·&nbsp; Generated {{ now()->format('d M Y H:i') }}
        &nbsp;·&nbsp; Academic Year: {{ $academicYear?->name ?? '—' }}
    </div>
</body>

</html>

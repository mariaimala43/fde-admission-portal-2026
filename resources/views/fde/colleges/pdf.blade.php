{{--
    Colleges PDF Export — Model or Ex-FG
    Used by: CollegeController@exportPdf  (DomPDF, A4 landscape)

    Variables:
      $institutions   – Collection with total_boys, total_girls, total_admitted attached
      $collegeType    – 'Model College' | 'Ex-FG College'
      $academicYear   – AcademicYear model|null
      $generatedAt    – Formatted string
      $totalAdmitted  – int
      $totalBoys      – int
      $totalGirls     – int
--}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $collegeType }} — Admission Report</title>
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
        }

        .header {
            background: #1e3a8a;
            color: #fff;
            padding: 10px 14px;
            margin-bottom: 8px;
        }

        .header h1 {
            font-size: 12px;
            font-weight: 700;
        }

        .header .sub {
            font-size: 8px;
            opacity: .80;
            margin-top: 3px;
        }

        .header .meta {
            float: right;
            text-align: right;
            font-size: 7px;
            opacity: .85;
        }

        .kpi-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            border-spacing: 4px;
        }

        .kpi-cell {
            display: table-cell;
            width: 25%;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 4px;
            padding: 5px 8px;
            text-align: center;
        }

        .kpi-cell .num {
            font-size: 13px;
            font-weight: 700;
            color: #1e40af;
        }

        .kpi-cell .lbl {
            font-size: 6.5px;
            color: #6b7280;
            margin-top: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: #1e3a8a;
            color: #fff;
        }

        thead th {
            padding: 5px 6px;
            text-align: left;
            font-size: 7px;
            font-weight: 700;
            white-space: nowrap;
            text-transform: uppercase;
        }

        tbody tr:nth-child(even) {
            background: #f0f4ff;
        }

        tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        tbody td {
            padding: 4px 6px;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
            font-size: 7.5px;
        }

        .boys {
            color: #0369a1;
            font-weight: 600;
        }

        .girls {
            color: #be185d;
            font-weight: 600;
        }

        .total {
            color: #166534;
            font-weight: 700;
        }

        .mono {
            font-family: Courier New, monospace;
            font-size: 7px;
        }

        .footer {
            margin-top: 8px;
            font-size: 7px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 4px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="meta">
            Generated: {{ $generatedAt }}<br>
            Year: {{ $academicYear?->name ?? '—' }}<br>
            Total: {{ $institutions->count() }} colleges
        </div>
        <h1>{{ $collegeType }} — Admission Report</h1>
        <p class="sub">Federal Directorate of Education, ICT Islamabad</p>
    </div>

    {{-- KPI row --}}
    <div class="kpi-row">
        <div class="kpi-cell">
            <div class="num">{{ $institutions->count() }}</div>
            <div class="lbl">Total Colleges</div>
        </div>
        <div class="kpi-cell">
            <div class="num">{{ number_format($totalAdmitted) }}</div>
            <div class="lbl">Total Admitted</div>
        </div>
        <div class="kpi-cell" style="background:#eff6ff;border-color:#bae6fd;">
            <div class="num" style="color:#0369a1;">{{ number_format($totalBoys) }}</div>
            <div class="lbl">Boys</div>
        </div>
        <div class="kpi-cell" style="background:#fdf2f8;border-color:#fbcfe8;">
            <div class="num" style="color:#be185d;">{{ number_format($totalGirls) }}</div>
            <div class="lbl">Girls</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:3%">#</th>
                <th style="width:20%">College Name</th>
                <th style="width:5%">EMIS</th>
                <th style="width:5%">IB No.</th>
                <th style="width:5%">Gender</th>
                <th style="width:9%">UC</th>
                <th style="width:9%">Sector</th>
                <th style="width:16%">Principal / HOI</th>
                <th style="width:9%">Contact</th>
                <th style="width:4%" align="center">Boys</th>
                <th style="width:4%" align="center">Girls</th>
                <th style="width:4%" align="center">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($institutions as $i => $inst)
                @php
                    // Prefer dedicated model fields; fall back to linked user record
                    $hoiName =
                        $inst->hoi_name ?:
                        $inst->users->first(fn($u) => $u->hasRole('hoi') && $u->is_active)?->name ?? '—';
                    $hoiContact =
                        $inst->hoi_contact ?:
                        $inst->users->first(fn($u) => $u->hasRole('hoi') && $u->is_active)?->phone ?? '—';
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $inst->name }}</td>
                    <td class="mono">{{ $inst->code ?? '—' }}</td>
                    <td class="mono">{{ $inst->ib_number ?? '—' }}</td>
                    <td>
                        @if ($inst->gender === 'boys')
                            Boys
                        @elseif ($inst->gender === 'girls')
                            Girls
                        @else
                            Co-Ed
                        @endif
                    </td>
                    <td>{{ $inst->unionCouncil?->code ?? '—' }}</td>
                    <td>{{ $inst->sector?->name ?? '—' }}</td>
                    <td>{{ $hoiName }}</td>
                    <td class="mono">{{ $hoiContact }}</td>
                    <td class="boys" align="center">{{ number_format($inst->total_boys) }}</td>
                    <td class="girls" align="center">{{ number_format($inst->total_girls) }}</td>
                    <td class="total" align="center">{{ number_format($inst->total_admitted) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" align="center" style="padding:10px;color:#9ca3af;">
                        No colleges found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Federal Directorate of Education &mdash; {{ $collegeType }} Report &mdash; {{ $generatedAt }}
    </div>

</body>

</html>

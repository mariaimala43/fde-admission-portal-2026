<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1a1a2e; }
    .header { background: #0a1628; color: #fff; padding: 10px 14px; margin-bottom: 8px; }
    .header h1 { font-size: 13px; font-weight: bold; }
    .header p  { font-size: 9px; color: #93c5fd; margin-top: 2px; }
    .cards { display: flex; gap: 6px; margin-bottom: 10px; }
    .card { flex: 1; border: 1px solid #e5e7eb; border-radius: 6px; padding: 7px 10px; text-align: center; }
    .card .label { font-size: 7px; color: #6b7280; text-transform: uppercase; letter-spacing: .5px; }
    .card .value { font-size: 16px; font-weight: bold; margin: 3px 0 1px; }
    .card.blue   .value { color: #1d4ed8; }
    .card.purple .value { color: #7c3aed; }
    .card.orange .value { color: #c2410c; }
    .card.dark   { background: #0a1628; border-color: #0a1628; }
    .card.dark .label { color: #93c5fd; }
    .card.dark .value { color: #fff; font-size: 18px; }
    .section-title { font-size: 10px; font-weight: bold; color: #1e3a5f; margin-bottom: 4px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    table thead tr:first-child th { background: #1e3a5f; color: #fff; padding: 5px 6px; text-align: center; font-size: 7.5px; text-transform: uppercase; }
    table thead tr:last-child th  { background: #f3f4f6; color: #374151; padding: 3px 5px; text-align: center; font-size: 7px; }
    table tbody tr:nth-child(even) { background: #f9fafb; }
    table tbody td { padding: 4px 6px; font-size: 8px; text-align: center; border-bottom: 1px solid #f3f4f6; }
    table tbody td.cls-name { text-align: left; font-weight: 600; color: #1e293b; }
    table tbody td.orange { background: #fff7ed; color: #c2410c; }
    table tbody td.blue   { background: #eff6ff; color: #1d4ed8; }
    table tbody td.pink   { background: #eff6ff; color: #be185d; }
    table tbody td.purple { background: #faf5ff; color: #7c3aed; }
    table tbody td.purpleg{ background: #faf5ff; color: #6d28d9; }
    table tbody td.orng   { background: #fff7ed; color: #c2410c; }
    table tbody td.orngg  { background: #fff7ed; color: #9a3412; }
    table tbody td.total  { background: #dbeafe; font-weight: bold; color: #1e3a5f; }
    table tbody td.newadm { background: #eff6ff; font-weight: bold; color: #1e40af; }
    table tfoot td { background: #0a1628; color: #fff; font-weight: bold; font-size: 8px; padding: 5px 6px; text-align: center; }
    table tfoot td.orange { background: #92400e; }
    table tfoot td.purple { background: #5b21b6; }
    table tfoot td.orng   { background: #7c2d12; }
    .footer { margin-top: 8px; font-size: 7px; color: #9ca3af; text-align: right; }
    .period { font-size: 8px; color: #64748b; margin-bottom: 8px; }
</style>
</head>
<body>

<div class="header">
    <h1>Admission Report — {{ $institution->name }}</h1>
    <p>
        {{ $academicYear?->name ?? 'Academic Year' }}
        &nbsp;·&nbsp;
        Period: {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
        &nbsp;·&nbsp;
        Generated: {{ now()->format('d M Y, g:i A') }}
    </p>
</div>

{{-- Summary Cards --}}
<table style="margin-bottom:10px;">
    <tr>
        <td style="width:25%; background:#0a1628; color:#fff; text-align:center; padding:8px; border-radius:4px;">
            <div style="font-size:7px; color:#93c5fd; text-transform:uppercase;">Total Admitted</div>
            <div style="font-size:18px; font-weight:bold; margin:2px 0;">{{ number_format($grandTotal) }}</div>
            <div style="font-size:7px; color:#bfdbfe;">All categories · all shifts</div>
        </td>
        <td style="width:2%;"></td>
        <td style="width:23%; border:1px solid #dbeafe; text-align:center; padding:8px;">
            <div style="font-size:7px; color:#6b7280; text-transform:uppercase;">Regular</div>
            <div style="font-size:16px; font-weight:bold; color:#1d4ed8; margin:2px 0;">{{ number_format($grandRegular) }}</div>
        </td>
        <td style="width:2%;"></td>
        <td style="width:23%; border:1px solid #ede9fe; text-align:center; padding:8px;">
            <div style="font-size:7px; color:#6b7280; text-transform:uppercase;">OOSC</div>
            <div style="font-size:16px; font-weight:bold; color:#7c3aed; margin:2px 0;">{{ number_format($grandOosc) }}</div>
        </td>
        <td style="width:2%;"></td>
        <td style="width:23%; border:1px solid #ffedd5; text-align:center; padding:8px;">
            <div style="font-size:7px; color:#6b7280; text-transform:uppercase;">P2G</div>
            <div style="font-size:16px; font-weight:bold; color:#c2410c; margin:2px 0;">{{ number_format($grandP2p) }}</div>
        </td>
    </tr>
</table>

{{-- Class-wise Summary --}}
<div class="section-title">Class-wise Enrollment Summary</div>
<table>
    <thead>
        <tr>
            <th rowspan="2" style="text-align:left;">Class</th>
            <th rowspan="2">Sec</th>
            <th rowspan="2" style="background:#92400e;">Existing Students</th>
            <th rowspan="2" style="background:#1e40af;">Newly Admitted</th>
            <th rowspan="2">Total Seats</th>
            <th colspan="2">Regular New Admitted</th>
            <th colspan="2" style="background:#5b21b6;">OOSC Out-of-School</th>
            <th colspan="2" style="background:#7c2d12;">P2G Private to Govt</th>
            <th rowspan="2">Total</th>
        </tr>
        <tr>
            <th>Boys</th>
            <th>Girls</th>
            <th style="background:#ede9fe; color:#5b21b6;">Boys</th>
            <th style="background:#ede9fe; color:#5b21b6;">Girls</th>
            <th style="background:#ffedd5; color:#7c2d12;">Boys</th>
            <th style="background:#ffedd5; color:#7c2d12;">Girls</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($classes as $ic)
            @php
                $s        = $classSummary[$ic->class_id] ?? null;
                $admitted = $s ? (int) $s->grand_total : 0;
                $secCount = \App\Models\InstitutionSection::where('institution_id', $ic->institution_id)
                                ->where('class_id', $ic->class_id)->count() ?: 1;
                $regBoys   = ($s?->morning_boys  ?? 0) + ($s?->evening_boys  ?? 0);
                $regGirls  = ($s?->morning_girls ?? 0) + ($s?->evening_girls ?? 0);
                $ooscBoys  = ($s?->morning_oosc_boys  ?? 0) + ($s?->evening_oosc_boys  ?? 0);
                $ooscGirls = ($s?->morning_oosc_girls ?? 0) + ($s?->evening_oosc_girls ?? 0);
                $p2pBoys   = ($s?->morning_p2p_boys  ?? 0) + ($s?->evening_p2p_boys  ?? 0);
                $p2pGirls  = ($s?->morning_p2p_girls ?? 0) + ($s?->evening_p2p_girls ?? 0);
            @endphp
            <tr>
                <td class="cls-name">{{ $ic->classModel?->name }}</td>
                <td>{{ $secCount }}</td>
                <td class="orange">{{ number_format($ic->existing_enrollment) }}</td>
                <td class="newadm">{{ number_format($admitted) }}</td>
                <td>{{ number_format($ic->total_seats) }}</td>
                <td class="blue">{{ number_format($regBoys) }}</td>
                <td class="pink">{{ number_format($regGirls) }}</td>
                <td class="purple">{{ number_format($ooscBoys) }}</td>
                <td class="purpleg">{{ number_format($ooscGirls) }}</td>
                <td class="orng">{{ number_format($p2pBoys) }}</td>
                <td class="orngg">{{ number_format($p2pGirls) }}</td>
                <td class="total">{{ number_format($admitted) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" style="text-align:left;">GRAND TOTAL</td>
            <td class="orange">{{ number_format($classes->sum('existing_enrollment')) }}</td>
            <td>{{ number_format($grandTotal) }}</td>
            <td>{{ number_format($classes->sum('total_seats')) }}</td>
            <td>{{ number_format($classSummary->sum('morning_boys') + $classSummary->sum('evening_boys')) }}</td>
            <td>{{ number_format($classSummary->sum('morning_girls') + $classSummary->sum('evening_girls')) }}</td>
            <td class="purple">{{ number_format($classSummary->sum('morning_oosc_boys') + $classSummary->sum('evening_oosc_boys')) }}</td>
            <td class="purple">{{ number_format($classSummary->sum('morning_oosc_girls') + $classSummary->sum('evening_oosc_girls')) }}</td>
            <td class="orng">{{ number_format($classSummary->sum('morning_p2p_boys') + $classSummary->sum('evening_p2p_boys')) }}</td>
            <td class="orng">{{ number_format($classSummary->sum('morning_p2p_girls') + $classSummary->sum('evening_p2p_girls')) }}</td>
            <td>{{ number_format($grandTotal) }}</td>
        </tr>
    </tfoot>
</table>

<div class="footer">FDE Admission Portal &nbsp;·&nbsp; {{ $institution->name }} &nbsp;·&nbsp; {{ now()->format('d M Y') }}</div>

</body>
</html>

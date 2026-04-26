<!DOCTYPE html>
{{-- SAVE AS: resources/views/fde/rooms/pdf.blade.php --}}
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>New Construction Rooms — FDE Cell</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            color: #1a1a2e;
            background: #fff;
        }

        /* ── Page Header ── */
        .page-header {
            background: #1e3a5f;
            color: #fff;
            padding: 10px 14px 8px;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .page-header h1 {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.3px;
        }

        .page-header p {
            font-size: 8px;
            color: #b8cfe8;
            margin-top: 2px;
        }

        .header-meta {
            float: right;
            text-align: right;
            font-size: 7.5px;
            color: #b8cfe8;
            margin-top: -28px;
        }

        .clearfix::after {
            content: '';
            display: table;
            clear: both;
        }

        /* ── Summary Strip ── */
        .summary-strip {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border-collapse: separate;
            border-spacing: 4px;
        }

        .summary-cell {
            display: table-cell;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 5px 8px;
            text-align: center;
            width: 16.6%;
        }

        .summary-cell .label {
            font-size: 6.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #64748b;
        }

        .summary-cell .value {
            font-size: 13px;
            font-weight: bold;
            color: #1e3a5f;
            margin-top: 1px;
        }

        .summary-cell.green .value {
            color: #16a34a;
        }

        .summary-cell.yellow .value {
            color: #ca8a04;
        }

        .summary-cell.dark {
            background: #1e3a5f;
        }

        .summary-cell.dark .label {
            color: #93c5fd;
        }

        .summary-cell.dark .value {
            color: #fff;
        }

        /* ── Section headings ── */
        .section-title {
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 4px 8px;
            margin-bottom: 4px;
            border-radius: 3px;
        }

        .section-completed {
            background: #dcfce7;
            color: #15803d;
        }

        .section-near {
            background: #fef9c3;
            color: #a16207;
        }

        /* ── Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        thead tr {
            background: #1e3a5f;
            color: #fff;
        }

        thead th {
            padding: 5px 6px;
            text-align: left;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        thead th.center {
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        tbody td {
            padding: 4px 6px;
            font-size: 8.5px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        tbody td.center {
            text-align: center;
        }

        .rooms-badge {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            font-weight: bold;
            padding: 1px 6px;
            border-radius: 10px;
            font-size: 8px;
        }

        .status-badge {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 10px;
            font-size: 7.5px;
            font-weight: bold;
        }

        .status-completed {
            background: #dcfce7;
            color: #15803d;
        }

        .status-near {
            background: #fef9c3;
            color: #a16207;
        }

        .sector-tag {
            font-size: 7.5px;
            color: #64748b;
        }

        tfoot td {
            padding: 5px 6px;
            font-size: 8.5px;
            font-weight: bold;
            background: #e0e7ef;
            border-top: 2px solid #1e3a5f;
        }

        /* ── Footer ── */
        .page-footer {
            font-size: 7px;
            color: #94a3b8;
            text-align: center;
            margin-top: 6px;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }

        .filter-note {
            font-size: 7.5px;
            color: #64748b;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            padding: 3px 7px;
            margin-bottom: 8px;
            display: inline-block;
        }
    </style>
</head>

<body>

    {{-- ── Page Header ── --}}
    <div class="page-header clearfix">
        <div class="header-meta">
            Generated: {{ $generatedAt }}<br>
            FDE Cell — ICT Schools
        </div>
        <h1>🏗 New Construction Classrooms — ICT / FDE</h1>
        <p>Provision of Basic Educational Facilities in Educational Institutions of ICT under FDE</p>
    </div>

    {{-- ── Active filters note ── --}}
    @if ($search || $status)
        <div class="filter-note">
            Filtered by:
            @if ($search)
                <strong>Search:</strong> {{ $search }}
            @endif
            @if ($status)
                &nbsp;|&nbsp; <strong>Status:</strong> {{ $status === 'completed' ? 'Completed' : 'Near Completion' }}
            @endif
        </div>
    @endif

    {{-- ── Summary Strip ── --}}
    @php
        $totalSchools = $records->count();
        $totalRooms = $records->sum('rooms_total');
        $allocatedRooms = $records->sum('rooms_allocated');
        $completedCount = $records->where('construction_status', 'completed')->count();
        $nearCount = $records->where('construction_status', 'near_completion')->count();
        $totalSeats = $totalRooms * 40;
    @endphp
    <table class="summary-strip">
        <tr>
            <td class="summary-cell">
                <div class="label">Schools</div>
                <div class="value">{{ $totalSchools }}</div>
            </td>
            <td class="summary-cell">
                <div class="label">Total Rooms</div>
                <div class="value">{{ number_format($totalRooms) }}</div>
            </td>
            <td class="summary-cell green">
                <div class="label">Completed</div>
                <div class="value">{{ $completedCount }}</div>
            </td>
            <td class="summary-cell yellow">
                <div class="label">Near Completion</div>
                <div class="value">{{ $nearCount }}</div>
            </td>
            <td class="summary-cell">
                <div class="label">Rooms Allocated</div>
                <div class="value">{{ number_format($allocatedRooms) }}</div>
            </td>
            <td class="summary-cell dark">
                <div class="label">Capacity Added</div>
                <div class="value">{{ number_format($totalSeats) }}</div>
            </td>
        </tr>
    </table>

    {{-- ── Completed Schools ── --}}
    @php $completed = $records->where('construction_status', 'completed'); @endphp
    @if ($completed->count())
        <div class="section-title section-completed">
            ✅ &nbsp; Completed Construction — {{ $completed->count() }} Schools &nbsp;|&nbsp;
            {{ $completed->sum('rooms_total') }} Rooms
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width:22px;">#</th>
                    <th>School / Institution</th>
                    <th>Sector</th>
                    <th>Type</th>
                    <th class="center">Total<br>Rooms</th>
                    <th class="center">Allocated<br>Rooms</th>
                    <th class="center">Remaining</th>
                    <th class="center">Seat<br>Capacity</th>
                    <th class="center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($completed as $i => $room)
                    <tr>
                        <td class="center" style="color:#94a3b8;">{{ $i + 1 }}</td>
                        <td>
                            <strong>{{ $room->institution?->name ?? '—' }}</strong><br>
                            <span class="sector-tag">
                                {{ $room->institution?->type }}
                                {{ $room->institution?->gender ? '· ' . ucfirst(str_replace('_', ' ', $room->institution->gender)) : '' }}
                            </span>
                        </td>
                        <td class="sector-tag">{{ $room->institution?->sector?->name ?? '—' }}</td>
                        <td class="sector-tag">{{ $room->institution?->type ?? '—' }}</td>
                        <td class="center"><span class="rooms-badge">{{ $room->rooms_total }}</span></td>
                        <td class="center">{{ $room->rooms_allocated ?: '—' }}</td>
                        <td class="center">
                            @php $rem = $room->rooms_total - $room->rooms_allocated; @endphp
                            {{ $rem > 0 ? $rem : '—' }}
                        </td>
                        <td class="center">{{ number_format($room->rooms_total * 40) }}</td>
                        <td class="center">
                            <span class="status-badge status-completed">Completed</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;">TOTAL</td>
                    <td class="center">{{ $completed->sum('rooms_total') }}</td>
                    <td class="center">{{ $completed->sum('rooms_allocated') }}</td>
                    <td class="center">{{ $completed->sum('rooms_total') - $completed->sum('rooms_allocated') }}</td>
                    <td class="center">{{ number_format($completed->sum('rooms_total') * 40) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- ── Near Completion Schools ── --}}
    @php $near = $records->where('construction_status', 'near_completion'); @endphp
    @if ($near->count())
        <div class="section-title section-near">
            🔨 &nbsp; Near Completion — {{ $near->count() }} Schools &nbsp;|&nbsp;
            {{ $near->sum('rooms_total') }} Rooms
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width:22px;">#</th>
                    <th>School / Institution</th>
                    <th>Sector</th>
                    <th>Type</th>
                    <th class="center">Total<br>Rooms</th>
                    <th class="center">Allocated<br>Rooms</th>
                    <th class="center">Remaining</th>
                    <th class="center">Seat<br>Capacity</th>
                    <th class="center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($near as $i => $room)
                    <tr>
                        <td class="center" style="color:#94a3b8;">{{ $i + 1 }}</td>
                        <td>
                            <strong>{{ $room->institution?->name ?? '—' }}</strong><br>
                            <span class="sector-tag">
                                {{ $room->institution?->type }}
                                {{ $room->institution?->gender ? '· ' . ucfirst(str_replace('_', ' ', $room->institution->gender)) : '' }}
                            </span>
                        </td>
                        <td class="sector-tag">{{ $room->institution?->sector?->name ?? '—' }}</td>
                        <td class="sector-tag">{{ $room->institution?->type ?? '—' }}</td>
                        <td class="center"><span class="rooms-badge">{{ $room->rooms_total }}</span></td>
                        <td class="center">{{ $room->rooms_allocated ?: '—' }}</td>
                        <td class="center">
                            @php $rem = $room->rooms_total - $room->rooms_allocated; @endphp
                            {{ $rem > 0 ? $rem : '—' }}
                        </td>
                        <td class="center">{{ number_format($room->rooms_total * 40) }}</td>
                        <td class="center">
                            <span class="status-badge status-near">Near Completion</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;">TOTAL</td>
                    <td class="center">{{ $near->sum('rooms_total') }}</td>
                    <td class="center">{{ $near->sum('rooms_allocated') }}</td>
                    <td class="center">{{ $near->sum('rooms_total') - $near->sum('rooms_allocated') }}</td>
                    <td class="center">{{ number_format($near->sum('rooms_total') * 40) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- ── Grand Total ── --}}
    <table>
        <tfoot>
            <tr>
                <td style="width:22px;"></td>
                <td><strong>GRAND TOTAL — {{ $totalSchools }} Schools</strong></td>
                <td></td>
                <td></td>
                <td class="center"><strong>{{ number_format($totalRooms) }}</strong></td>
                <td class="center"><strong>{{ number_format($allocatedRooms) }}</strong></td>
                <td class="center"><strong>{{ number_format($totalRooms - $allocatedRooms) }}</strong></td>
                <td class="center"><strong>{{ number_format($totalSeats) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{-- ── Footer ── --}}
    <div class="page-footer">
        Federal Directorate of Education (FDE) — ICT, Islamabad &nbsp;|&nbsp;
        Provision of Basic Educational Facilities &nbsp;|&nbsp;
        Generated: {{ $generatedAt }}
    </div>

</body>

</html>

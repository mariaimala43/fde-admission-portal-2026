{{--
    UC Control Rooms — PDF Export
    Used by: UcControlRoomController@exportPdf  (DomPDF, A4 landscape)

    Variables:
      $records     – Collection of UcControlRoom models (with unionCouncil)
      $generatedAt – Formatted date-time string
      $search      – Active search filter (may be null)
      $org         – Active organisation filter (may be null)
--}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>UC Control Rooms — Focal Persons Directory</title>
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
            letter-spacing: 0.5px;
        }

        .header .sub {
            font-size: 8px;
            opacity: .80;
            margin-top: 3px;
        }

        .header .meta {
            float: right;
            text-align: right;
            font-size: 7.5px;
            opacity: .85;
        }

        .filter-note {
            font-size: 7px;
            color: #6b7280;
            margin-bottom: 6px;
            font-style: italic;
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
            letter-spacing: 0.3px;
        }

        tbody tr:nth-child(even) {
            background: #f0f4ff;
        }

        tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        tbody td {
            padding: 5px 6px;
            vertical-align: top;
            border-bottom: 1px solid #e5e7eb;
            font-size: 7.5px;
            line-height: 1.4;
        }

        .uc-code {
            font-weight: 700;
            color: #1e3a8a;
            font-size: 7.5px;
        }

        .uc-name {
            color: #6b7280;
            font-size: 6.5px;
        }

        .org-badge {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 3px;
            padding: 1px 4px;
            font-size: 6.5px;
            font-weight: 700;
        }

        .contact {
            font-family: Courier New, monospace;
            font-size: 7px;
            color: #374151;
        }

        .section-label {
            font-size: 6.5px;
            color: #9ca3af;
            text-transform: uppercase;
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

    {{-- Header --}}
    <div class="header">
        <div class="meta">
            Generated: {{ $generatedAt }}<br>
            Records: {{ $records->count() }}
        </div>
        <h1>NO CHILD LEFT BEHIND &mdash; UC Control Room Focal Persons</h1>
        <p class="sub">Federal Directorate of Education, ICT Islamabad &mdash; NCLB Programme</p>
    </div>

    {{-- Filter note --}}
    @if ($search || $org)
        <p class="filter-note">
            Filtered by:
            @if ($search)
                search "{{ $search }}"
            @endif
            @if ($org)
                organisation "{{ $org }}"
            @endif
        </p>
    @endif

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th style="width:7%">UC</th>
                <th style="width:8%">Organisation</th>
                <th style="width:12%">Org Focal Person</th>
                <th style="width:11%">Org Contact</th>
                <th style="width:15%">FDE School</th>
                <th style="width:14%">FDE Focal Person</th>
                <th style="width:10%">FDE Contact</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $rec)
                <tr>
                    <td>
                        <div class="uc-code">{{ $rec->unionCouncil?->code ?? '—' }}</div>
                        <div class="uc-name">{{ $rec->unionCouncil?->name ?? '' }}</div>
                    </td>
                    <td>
                        <span class="org-badge">{{ $rec->organization_name ?? '—' }}</span>
                    </td>
                    <td>{{ $rec->focal_person_name ?? '—' }}</td>
                    <td class="contact">{{ $rec->focal_person_contact ?? '—' }}</td>
                    <td>{{ $rec->fde_school_name ?? '—' }}</td>
                    <td>{{ $rec->fde_focal_person_name ?? '—' }}</td>
                    <td class="contact">{{ $rec->fde_focal_person_contact ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:10px;color:#9ca3af;">
                        No records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Federal Directorate of Education &mdash; NCLB Programme &mdash; UC Control Room Directory &mdash;
        {{ $generatedAt }}
    </div>

</body>

</html>

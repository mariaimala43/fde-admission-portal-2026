<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Strength Register — {{ $register->institution->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 9px; color: #1a1a1a; background: #fff; }
        .header { text-align: center; margin-bottom: 10px; border-bottom: 2px solid #1a56db; padding-bottom: 8px; }
        .header h1 { font-size: 14px; font-weight: bold; color: #1a56db; }
        .header h2 { font-size: 11px; font-weight: bold; margin-top: 2px; }
        .header p { font-size: 9px; color: #555; margin-top: 2px; }
        .meta { display: flex; gap: 30px; margin-bottom: 10px; font-size: 9px; }
        .meta-item { display: inline-block; }
        .meta-label { color: #777; }
        .meta-value { font-weight: bold; }
        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 8px; font-weight: bold; }
        .status-locked { background: #dbeafe; color: #1d4ed8; }
        .status-submitted { background: #dcfce7; color: #15803d; }
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .section-title { font-size: 10px; font-weight: bold; margin: 10px 0 4px; color: #374151; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th { background: #eff6ff; color: #374151; font-size: 7.5px; font-weight: bold; text-transform: uppercase; padding: 4px 3px; text-align: center; border: 1px solid #d1d5db; }
        th.left { text-align: left; }
        td { padding: 3px; border: 1px solid #e5e7eb; font-size: 8.5px; text-align: center; }
        td.name { text-align: left; font-weight: 500; padding-left: 5px; }
        td.vacant { background: #fffbeb; color: #b45309; font-weight: bold; }
        tr:nth-child(even) td { background: #f9fafb; }
        tr:nth-child(even) td.vacant { background: #fffbeb; }
        .total-row { background: #eff6ff; font-weight: bold; font-size: 10px; padding: 6px 16px; border: 1px solid #bfdbfe; border-radius: 6px; display: flex; justify-content: space-between; }
        .remarks { background: #fffbeb; border: 1px solid #fde68a; border-radius: 4px; padding: 6px 10px; margin-bottom: 10px; font-size: 8.5px; }
        .remarks-label { font-weight: bold; color: #92400e; margin-bottom: 2px; }
        .footer { margin-top: 15px; border-top: 1px solid #e5e7eb; padding-top: 6px; display: flex; justify-content: space-between; font-size: 8px; color: #9ca3af; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Federal Directorate of Education</h1>
        <h2>Staff Strength Register</h2>
        <p>{{ $register->institution->name }} — EMIS: {{ $register->institution->code }}</p>
    </div>

    <div class="meta">
        <span class="meta-item"><span class="meta-label">Type: </span><span class="meta-value">{{ $register->institution->type }}</span></span>
        <span class="meta-item"><span class="meta-label">Sector: </span><span class="meta-value">{{ $register->institution->sector->name ?? '—' }}</span></span>
        <span class="meta-item"><span class="meta-label">Academic Year: </span><span class="meta-value">{{ $register->academicYear->name }}</span></span>
        <span class="meta-item">
            <span class="meta-label">Status: </span>
            <span class="status-badge status-{{ $register->status }}">{{ ucfirst($register->status) }}</span>
        </span>
        @if($register->submitted_at)
        <span class="meta-item"><span class="meta-label">Submitted: </span><span class="meta-value">{{ $register->submitted_at->format('d M Y') }}</span></span>
        @endif
    </div>

    @if($register->fde_remarks)
        <div class="remarks">
            <div class="remarks-label">FDE Remarks</div>
            <div>{{ $register->fde_remarks }}</div>
        </div>
    @endif

    <div class="section-title">Section A — Teaching &amp; Academic Posts</div>
    <table>
        <thead>
            <tr>
                <th class="left" style="width:120px">Post</th>
                <th>Sanctioned</th>
                <th>Filled</th>
                <th style="background:#fffbeb;color:#b45309;">Vacant</th>
                <th>Sacked</th>
                <th>DW-IN</th>
                <th>DW-OUT</th>
                <th>Study Leave</th>
                <th>Dep-IN</th>
                <th>Dep-OUT</th>
                <th>Temp-IN</th>
                <th>Temp-OUT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($teachingEntries as $entry)
            <tr>
                <td class="name">{{ $entry->postType->name }}</td>
                <td>{{ $entry->sanctioned_posts }}</td>
                <td>{{ $entry->filled_posts }}</td>
                <td class="vacant">{{ $entry->vacant_posts }}</td>
                <td>{{ $entry->sacked_employees }}</td>
                <td>{{ $entry->daily_wagers_in }}</td>
                <td>{{ $entry->daily_wagers_out }}</td>
                <td>{{ $entry->study_leave }}</td>
                <td>{{ $entry->deputationist_in }}</td>
                <td>{{ $entry->deputationist_out }}</td>
                <td>{{ $entry->temporary_in }}</td>
                <td>{{ $entry->temporary_out }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Section B — Program Posts</div>
    <table style="width:40%">
        <thead>
            <tr>
                <th class="left">Program</th>
                <th>Number of Posts</th>
            </tr>
        </thead>
        <tbody>
            @foreach($programEntries as $entry)
            <tr>
                <td class="name">{{ $entry->postType->name }}</td>
                <td>{{ $entry->number_of_posts }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-row">
        <span>Total Staff Physically Present on Duty</span>
        <span>{{ number_format($register->totalPresentOnDuty()) }}</span>
    </div>

    <div class="footer">
        <span>Generated: {{ now()->format('d M Y, H:i') }}</span>
        <span>Federal Directorate of Education — Staff Strength Register</span>
    </div>

</body>
</html>

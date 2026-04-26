<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correction Decision</title>
    <style>
        body  { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .wrap { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .hdr  { padding: 24px 32px; color: #fff; }
        .hdr.approved { background: #16a34a; }
        .hdr.rejected { background: #dc2626; }
        .hdr h1 { margin: 0; font-size: 22px; }
        .body { padding: 24px 32px; color: #374151; }
        .body p { margin: 0 0 14px; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { text-align: left; padding: 8px 12px; border: 1px solid #e5e7eb; font-size: 13px; }
        th { background: #f9fafb; font-weight: 600; color: #4b5563; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 700; }
        .badge.approved { background: #dcfce7; color: #15803d; }
        .badge.rejected { background: #fee2e2; color: #b91c1c; }
        .note-box { background: #fef9c3; border-left: 4px solid #eab308; padding: 12px 16px; border-radius: 4px; margin: 16px 0; font-size: 13px; }
        .footer { padding: 16px 32px; background: #f9fafb; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="wrap">

    {{-- Header --}}
    <div class="hdr {{ $decision }}">
        <h1>
            @if ($decision === 'approved')
                ✅ Correction Approved
            @else
                ❌ Correction Rejected
            @endif
        </h1>
    </div>

    {{-- Body --}}
    <div class="body">
        <p>
            Dear <strong>{{ $correction->requestedBy?->name ?? 'Head of Institution' }}</strong>,
        </p>
        <p>
            Your admission correction request for
            <strong>{{ $correction->institution?->name }}</strong>
            has been reviewed by the FDE Cell.
            Status: <span class="badge {{ $decision }}">{{ ucfirst($decision) }}</span>
        </p>

        {{-- Correction details --}}
        <table>
            <tr><th>Date</th>      <td>{{ $correction->admission_date?->format('d M Y') }}</td></tr>
            <tr><th>Class</th>     <td>{{ $correction->classModel?->name }}</td></tr>
            <tr><th>Submitted</th> <td>{{ $correction->created_at?->timezone('Asia/Karachi')->format('d M Y, h:i A') }}</td></tr>
            <tr><th>Reviewed</th>  <td>{{ $correction->reviewed_at?->timezone('Asia/Karachi')->format('d M Y, h:i A') }}</td></tr>
        </table>

        {{-- FDE note --}}
        @if ($correction->fde_note)
            <div class="note-box">
                <strong>FDE Note:</strong> {{ $correction->fde_note }}
            </div>
        @endif

        @if ($decision === 'approved')
            <p>
                The admission data for the above record has been updated with the corrected values.
                No further action is required on your end.
            </p>
        @else
            <p>
                Your correction request was not approved. Please review the FDE note above
                and contact the FDE Cell if you have questions.
            </p>
        @endif

        <p>
            You can view the full correction history in the
            <strong>FDE Admission Portal → Corrections</strong> section.
        </p>
    </div>

    <div class="footer">
        FDE Admission Portal &mdash; This is an automated notification. Do not reply to this email.
    </div>

</div>
</body>
</html>

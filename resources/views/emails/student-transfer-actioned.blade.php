<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Update</title>
    <style>
        body  { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .wrap { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .hdr  { padding: 24px 32px; color: #fff; }
        .hdr.accepted  { background: #16a34a; }
        .hdr.rejected  { background: #dc2626; }
        .hdr.cancelled { background: #6b7280; }
        .hdr h1 { margin: 0; font-size: 22px; }
        .body { padding: 24px 32px; color: #374151; }
        .body p { margin: 0 0 14px; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { text-align: left; padding: 8px 12px; border: 1px solid #e5e7eb; font-size: 13px; }
        th { background: #f9fafb; font-weight: 600; color: #4b5563; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 700; }
        .badge.accepted  { background: #dcfce7; color: #15803d; }
        .badge.rejected  { background: #fee2e2; color: #b91c1c; }
        .badge.cancelled { background: #f3f4f6; color: #374151; }
        .note-box { background: #fef9c3; border-left: 4px solid #eab308; padding: 12px 16px; border-radius: 4px; margin: 16px 0; font-size: 13px; }
        .footer { padding: 16px 32px; background: #f9fafb; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="wrap">

    {{-- Header --}}
    <div class="hdr {{ $action }}">
        <h1>
            @if ($action === 'accepted')  ✅ Transfer Accepted
            @elseif ($action === 'rejected')  ❌ Transfer Rejected
            @else  🚫 Transfer Cancelled
            @endif
        </h1>
    </div>

    {{-- Body --}}
    <div class="body">
        <p>
            Dear <strong>{{ $transfer->initiatedBy?->name ?? 'Head of Institution' }}</strong>,
        </p>
        <p>
            A student transfer request you initiated has been
            <span class="badge {{ $action }}">{{ ucfirst($action) }}</span>.
        </p>

        {{-- Transfer details --}}
        <table>
            <tr><th>Student</th>        <td>{{ $transfer->student_name ?? '—' }}</td></tr>
            <tr><th>Father's Name</th>  <td>{{ $transfer->father_name  ?? '—' }}</td></tr>
            <tr><th>Class</th>          <td>{{ $transfer->classModel?->name ?? '—' }}</td></tr>
            <tr><th>From</th>           <td>{{ $transfer->fromInstitution?->name }}</td></tr>
            <tr><th>To</th>             <td>{{ $transfer->toInstitution?->name }}</td></tr>
            <tr><th>Initiated</th>      <td>{{ $transfer->created_at?->timezone('Asia/Karachi')->format('d M Y, h:i A') }}</td></tr>
        </table>

        @if ($action === 'rejected' && $transfer->rejection_reason)
            <div class="note-box">
                <strong>Rejection Reason:</strong> {{ $transfer->rejection_reason }}
            </div>
        @endif

        @if ($action === 'cancelled' && $transfer->cancellation_reason)
            <div class="note-box">
                <strong>Cancellation Reason:</strong> {{ $transfer->cancellation_reason }}
            </div>
        @endif

        @if ($action === 'accepted')
            <p>
                The enrollment counts for both schools have been updated automatically.
                No further action is required.
            </p>
        @elseif ($action === 'rejected')
            <p>
                The transfer request was declined by the receiving school. Please review the reason above
                and contact the FDE Cell if you believe this is an error.
            </p>
        @endif

        <p>
            View the full transfer record in the
            <strong>FDE Admission Portal → Transfers</strong> section.
        </p>
    </div>

    <div class="footer">
        FDE Admission Portal &mdash; This is an automated notification. Do not reply to this email.
    </div>

</div>
</body>
</html>

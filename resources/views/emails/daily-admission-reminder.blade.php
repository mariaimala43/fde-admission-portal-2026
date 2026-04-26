<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Admission Reminder</title>
    <style>
        body  { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .wrap { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .hdr  { padding: 24px 32px; background: #d97706; color: #fff; }
        .hdr h1 { margin: 0; font-size: 20px; }
        .hdr p  { margin: 6px 0 0; font-size: 13px; opacity: .9; }
        .body { padding: 24px 32px; color: #374151; }
        .body p { margin: 0 0 14px; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { text-align: left; padding: 8px 12px; border: 1px solid #e5e7eb; font-size: 13px; }
        th { background: #f9fafb; font-weight: 600; color: #4b5563; }
        .cta { display: inline-block; margin: 8px 0 16px; padding: 12px 24px; background: #d97706; color: #fff; font-weight: 700; font-size: 14px; border-radius: 6px; text-decoration: none; }
        .cta:hover { background: #b45309; }
        .note-box { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 4px; margin: 16px 0; font-size: 13px; }
        .footer { padding: 16px 32px; background: #f9fafb; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="wrap">

    {{-- Header --}}
    <div class="hdr">
        <h1>🔔 Daily Admission Entry Pending</h1>
        <p>FDE Admission Portal 2026 — Action Required</p>
    </div>

    {{-- Body --}}
    <div class="body">
        <p>Dear <strong>{{ $user->name }}</strong>,</p>

        <p>
            You have <strong>not yet submitted</strong> today's admission data for
            <strong>{{ $schoolName }}</strong>.
            Please submit your entry before the daily cutoff time.
        </p>

        <table>
            <tr><th>School</th>        <td>{{ $schoolName }}</td></tr>
            <tr><th>Date</th>          <td>{{ now()->setTimezone('Asia/Karachi')->format('d F Y') }}</td></tr>
            <tr><th>Cutoff Time</th>   <td><strong style="color:#d97706;">{{ $cutoffTime }}</strong></td></tr>
        </table>

        <div class="note-box">
            ⚠️ <strong>Important:</strong> Submissions after {{ $cutoffTime }} may be marked as late.
            Contact the FDE Cell if you need an extension.
        </div>

        <p>Click the button below to open the admission entry form:</p>

        <a href="{{ url('/hoi/admissions/daily') }}" class="cta">
            Submit Today's Admissions →
        </a>

        <p style="font-size:13px; color:#6b7280;">
            If the button above doesn't work, copy and paste this link:<br>
            <a href="{{ url('/hoi/admissions/daily') }}" style="color:#d97706;">{{ url('/hoi/admissions/daily') }}</a>
        </p>
    </div>

    <div class="footer">
        FDE Admission Portal &mdash; This is an automated reminder. Do not reply to this email.
    </div>

</div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
</head>

<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: Arial, Helvetica, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color: #1e3a5f; padding: 30px 40px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 20px; font-weight: bold;">
                                Federal Directorate of Education
                            </h1>
                            <p style="color: #93c5fd; margin: 5px 0 0; font-size: 13px;">
                                Admission Portal 2026-27
                            </p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="color: #1f2937; font-size: 18px; margin: 0 0 15px;">
                                Password Reset Request
                            </h2>

                            <p style="color: #4b5563; font-size: 14px; line-height: 1.6; margin: 0 0 10px;">
                                Dear <strong>{{ $user->name }}</strong>,
                            </p>

                            <p style="color: #4b5563; font-size: 14px; line-height: 1.6; margin: 0 0 25px;">
                                We received a request to reset the password for your FDE Admission Portal account.
                                Click the button below to set a new password. This link will expire in
                                <strong>60 minutes</strong>.
                            </p>

                            {{-- Button --}}
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 10px 0 25px;">
                                        <a href="{{ $resetUrl }}"
                                            style="display: inline-block; background-color: #1e3a5f; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: bold; padding: 12px 35px; border-radius: 8px;">
                                            Reset Password
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #6b7280; font-size: 13px; line-height: 1.6; margin: 0 0 10px;">
                                If the button doesn't work, copy and paste this URL into your browser:
                            </p>
                            <p style="color: #3b82f6; font-size: 12px; word-break: break-all; margin: 0 0 25px; background-color: #f9fafb; padding: 10px; border-radius: 6px;">
                                {{ $resetUrl }}
                            </p>

                            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;" />

                            <p style="color: #9ca3af; font-size: 12px; line-height: 1.5; margin: 0;">
                                If you did not request a password reset, please ignore this email.
                                Your password will remain unchanged.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 40px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #9ca3af; font-size: 11px; margin: 0;">
                                &copy; {{ date('Y') }} Federal Directorate of Education — Admission Portal
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>

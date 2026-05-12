<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no,address=no,email=no,date=no,url=no">
    <title>Bug Report Received — {{ $ticket }}</title>
    <!--[if mso]>
    <xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml>
    <![endif]-->
    <style>
        /* Reset */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        /* Client-specific */
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        /* Media queries */
        @media only screen and (max-width: 600px) {
            .wrapper { width: 100% !important; max-width: 100% !important; }
            .responsive-table { width: 100% !important; }
            .mobile-padding { padding: 24px 16px !important; }
            .ticket-code { font-size: 26px !important; letter-spacing: 4px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f0f2f5;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">

    <!-- Preheader (hidden preview text) -->
    <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;">
        Your bug report has been received. Ticket: {{ $ticket }}. Track the status of your report anytime.
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
    </div>

    <!-- Wrapper -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f0f2f5;">
        <tr>
            <td align="center" style="padding:32px 16px;">

                <!-- Email card -->
                <table class="wrapper responsive-table" role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" style="max-width:600px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

                    <!-- Header brand bar -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#1e3a5f 0%,#2d6cdf 100%);padding:32px 40px;text-align:center;">
                            <p style="margin:0;font-size:11px;font-weight:700;letter-spacing:3px;color:rgba(255,255,255,0.7);text-transform:uppercase;">Bug Lifecycle System</p>
                            <h1 style="margin:8px 0 0;font-size:22px;font-weight:700;color:#ffffff;letter-spacing:-0.3px;">Bug Report Received</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="mobile-padding" style="padding:40px 40px 32px;">

                            <!-- Greeting -->
                            <p style="margin:0 0 20px;font-size:16px;color:#374151;line-height:1.6;">
                                Hello <strong>{{ $guestName }}</strong>,
                            </p>
                            <p style="margin:0 0 28px;font-size:15px;color:#6b7280;line-height:1.7;">
                                Thank you for submitting your bug report. We have received it and your ticket is now active. Here are your report details:
                            </p>

                            <!-- Ticket box -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
                                <tr>
                                    <td style="background:#f8faff;border:2px solid #dbeafe;border-radius:10px;padding:24px;text-align:center;">
                                        <p style="margin:0 0 6px;font-size:11px;font-weight:700;letter-spacing:2px;color:#6b7280;text-transform:uppercase;">Your Ticket Number</p>
                                        <p class="ticket-code" style="margin:0;font-size:32px;font-weight:800;letter-spacing:6px;color:#1e3a5f;font-family:'Courier New',monospace;">{{ $ticket }}</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Bug title -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
                                <tr>
                                    <td style="border-left:4px solid #2d6cdf;padding-left:16px;">
                                        <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:1.5px;color:#9ca3af;text-transform:uppercase;">Bug Title</p>
                                        <p style="margin:0;font-size:15px;font-weight:600;color:#111827;line-height:1.5;">{{ $title }}</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Status badge -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:32px;">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 10px;font-size:11px;font-weight:700;letter-spacing:1.5px;color:#9ca3af;text-transform:uppercase;">Current Status</p>
                                        <span style="display:inline-block;background:#fef3c7;color:#92400e;font-size:13px;font-weight:700;padding:6px 16px;border-radius:999px;letter-spacing:0.5px;">● Reported</span>
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA button -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $trackingUrl }}" target="_blank" style="display:inline-block;background:linear-gradient(135deg,#2d6cdf,#1e3a5f);color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 36px;border-radius:8px;letter-spacing:0.3px;">
                                            Track My Bug Report →
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:13px;color:#9ca3af;line-height:1.6;text-align:center;">
                                Or copy this link into your browser:<br>
                                <a href="{{ $trackingUrl }}" style="color:#2d6cdf;word-break:break-all;font-size:12px;">{{ $trackingUrl }}</a>
                            </p>

                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="padding:0 40px;">
                            <hr style="border:none;border-top:1px solid #f3f4f6;margin:0;">
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="mobile-padding" style="padding:24px 40px;text-align:center;">
                            <p style="margin:0 0 8px;font-size:13px;color:#6b7280;line-height:1.6;">
                                You received this email because you submitted a bug report.<br>
                                Please save your ticket number for future reference.
                            </p>
                            <p style="margin:0;font-size:12px;color:#9ca3af;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
                <!-- /Email card -->

            </td>
        </tr>
    </table>
    <!-- /Wrapper -->

</body>
</html>

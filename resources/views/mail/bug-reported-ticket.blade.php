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
            .header-padding { padding: 28px 20px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f4f2f3;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">

    <!-- Preheader (hidden preview text) -->
    <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;">
        Your bug report has been received. Ticket: {{ $ticket }}. Track the status of your report anytime.
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
    </div>

    <!-- Wrapper -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f4f2f3;">
        <tr>
            <td align="center" style="padding:40px 16px;">

                <!-- Email card -->
                <table class="wrapper responsive-table" role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" style="max-width:600px;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 32px rgba(138,11,78,0.06),0 1px 4px rgba(0,0,0,0.04);">

                    <!-- Header brand bar -->
                    <tr>
                        <td class="header-padding" style="background:linear-gradient(135deg,#3a021f 0%,#8a0b4e 50%,#b01567 100%);padding:36px 40px;text-align:center;">
                            <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:3.5px;color:rgba(255,255,255,0.55);text-transform:uppercase;">Bug Lifecycle</p>
                            <h1 style="margin:4px 0 0;font-size:24px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;">Report Received</h1>
                            <p style="margin:10px 0 0;font-size:13px;color:rgba(255,255,255,0.7);line-height:1.5;">We've got your submission — here's your ticket.</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="mobile-padding" style="padding:40px 40px 32px;">

                            <!-- Greeting -->
                            <p style="margin:0 0 20px;font-size:16px;color:#1e293b;line-height:1.6;">
                                Hello <strong>{{ $guestName }}</strong>,
                            </p>
                            <p style="margin:0 0 32px;font-size:15px;color:#64748b;line-height:1.75;">
                                Thank you for submitting your bug report. We have received it and your ticket is now active. Here are your report details:
                            </p>

                            <!-- Ticket box -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:32px;">
                                <tr>
                                    <td style="background:linear-gradient(135deg,#fdf2f8 0%,#f5e8ef 100%);border:1.5px solid #e8c6d8;border-radius:12px;padding:28px 24px;text-align:center;">
                                        <p style="margin:0 0 8px;font-size:10px;font-weight:800;letter-spacing:2.5px;color:#8a0b4e;text-transform:uppercase;">Your Ticket Number</p>
                                        <p class="ticket-code" style="margin:0;font-size:32px;font-weight:900;letter-spacing:6px;color:#3a021f;font-family:'Courier New',monospace;">{{ $ticket }}</p>
                                        <p style="margin:8px 0 0;font-size:11px;color:#94a3b8;letter-spacing:0.3px;">Save this for future tracking</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Bug title -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
                                <tr>
                                    <td style="border-left:3px solid #8a0b4e;padding:12px 0 12px 16px;">
                                        <p style="margin:0 0 4px;font-size:10px;font-weight:800;letter-spacing:2px;color:#94a3b8;text-transform:uppercase;">Bug Title</p>
                                        <p style="margin:0;font-size:15px;font-weight:600;color:#1e293b;line-height:1.5;">{{ $title }}</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Status badge -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:36px;">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 10px;font-size:10px;font-weight:800;letter-spacing:2px;color:#94a3b8;text-transform:uppercase;">Current Status</p>
                                        <span style="display:inline-block;background:#fef3c7;color:#92400e;font-size:13px;font-weight:700;padding:6px 18px;border-radius:999px;letter-spacing:0.3px;">● Reported</span>
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA button -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
                                <tr>
                                    <td align="center">
                                        <!--[if mso]>
                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $trackingUrl }}" style="height:48px;v-text-anchor:middle;width:240px;" arcsize="17%" fillcolor="#8a0b4e">
                                        <w:anchorlock/>
                                        <center style="color:#ffffff;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:15px;font-weight:bold;">Track My Bug Report →</center>
                                        </v:roundrect>
                                        <![endif]-->
                                        <!--[if !mso]><!-->
                                        <a href="{{ $trackingUrl }}" target="_blank" style="display:inline-block;background:linear-gradient(135deg,#8a0b4e 0%,#6d0940 100%);color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 40px;border-radius:8px;letter-spacing:0.3px;box-shadow:0 4px 14px rgba(138,11,78,0.25);transition:all 0.2s;">
                                            Track My Bug Report →
                                        </a>
                                        <!--<![endif]-->
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:13px;color:#94a3b8;line-height:1.6;text-align:center;">
                                Or copy this link into your browser:<br>
                                <a href="{{ $trackingUrl }}" style="color:#8a0b4e;word-break:break-all;font-size:12px;">{{ $trackingUrl }}</a>
                            </p>

                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="padding:0 40px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="border-top:1px solid #f1f5f9;font-size:0;line-height:0;height:1px;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="mobile-padding" style="padding:24px 40px 32px;text-align:center;">
                            <p style="margin:0 0 8px;font-size:13px;color:#64748b;line-height:1.65;">
                                You received this email because you submitted a bug report.<br>
                                Please save your ticket number for future reference.
                            </p>
                            <p style="margin:0;font-size:12px;color:#94a3b8;">
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

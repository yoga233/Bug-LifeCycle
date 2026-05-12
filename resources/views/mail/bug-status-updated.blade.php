<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no,address=no,email=no,date=no,url=no">
    <title>Bug Status Updated — {{ $ticket }}</title>
    <!--[if mso]>
    <xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml>
    <![endif]-->
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        @media only screen and (max-width: 600px) {
            .wrapper { width: 100% !important; max-width: 100% !important; }
            .responsive-table { width: 100% !important; }
            .mobile-padding { padding: 24px 16px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f0f2f5;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">

    @php
        /* Accent color per status */
        $accent = match($newStatus) {
            'Assigned'    => ['bg' => '#eff6ff', 'border' => '#bfdbfe', 'badge_bg' => '#dbeafe', 'badge_text' => '#1e40af', 'dot' => '#3b82f6', 'header_from' => '#1e3a5f', 'header_to' => '#2563eb'],
            'In Progress' => ['bg' => '#fff7ed', 'border' => '#fed7aa', 'badge_bg' => '#ffedd5', 'badge_text' => '#9a3412', 'dot' => '#f97316', 'header_from' => '#7c2d12', 'header_to' => '#ea580c'],
            'Testing'     => ['bg' => '#f5f3ff', 'border' => '#ddd6fe', 'badge_bg' => '#ede9fe', 'badge_text' => '#5b21b6', 'dot' => '#8b5cf6', 'header_from' => '#4c1d95', 'header_to' => '#7c3aed'],
            'Resolved'    => ['bg' => '#f0fdf4', 'border' => '#bbf7d0', 'badge_bg' => '#dcfce7', 'badge_text' => '#14532d', 'dot' => '#22c55e', 'header_from' => '#14532d', 'header_to' => '#16a34a'],
            default       => ['bg' => '#f8faff', 'border' => '#dbeafe', 'badge_bg' => '#dbeafe', 'badge_text' => '#1e40af', 'dot' => '#3b82f6', 'header_from' => '#1e3a5f', 'header_to' => '#2d6cdf'],
        };

        /* Progress steps */
        $steps = [
            ['label' => 'Reported',    'pct' => 10,  'status' => 'Reported'],
            ['label' => 'Assigned',    'pct' => 30,  'status' => 'Assigned'],
            ['label' => 'In Progress', 'pct' => 55,  'status' => 'In Progress'],
            ['label' => 'Testing',     'pct' => 80,  'status' => 'Testing'],
            ['label' => 'Resolved',    'pct' => 100, 'status' => 'Resolved'],
        ];

        $order = array_column($steps, 'status');
        $currentIdx = array_search($newStatus, $order, true);
    @endphp

    <!-- Preheader -->
    <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;">
        {{ $headline }} — Ticket {{ $ticket }}. Check the latest status of your bug report.
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
    </div>

    <!-- Wrapper -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f0f2f5;">
        <tr>
            <td align="center" style="padding:32px 16px;">

                <!-- Email card -->
                <table class="wrapper responsive-table" role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" style="max-width:600px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

                    <!-- Gradient header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,{{ $accent['header_from'] }} 0%,{{ $accent['header_to'] }} 100%);padding:32px 40px;text-align:center;">
                            <p style="margin:0;font-size:11px;font-weight:700;letter-spacing:3px;color:rgba(255,255,255,0.75);text-transform:uppercase;">Bug Lifecycle System</p>
                            <h1 style="margin:8px 0 0;font-size:22px;font-weight:700;color:#ffffff;letter-spacing:-0.3px;">Status Update</h1>
                            <p style="margin:8px 0 0;font-size:13px;color:rgba(255,255,255,0.8);">Ticket: <strong>{{ $ticket }}</strong></p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="mobile-padding" style="padding:36px 40px 28px;">

                            <!-- Greeting -->
                            <p style="margin:0 0 16px;font-size:16px;color:#374151;line-height:1.6;">
                                Hello <strong>{{ $guestName }}</strong>,
                            </p>
                            <p style="margin:0 0 28px;font-size:15px;color:#6b7280;line-height:1.7;">
                                {{ $headline }}. Here's the latest update on your bug report:
                            </p>

                            <!-- Bug title -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:24px;">
                                <tr>
                                    <td style="border-left:4px solid {{ $accent['dot'] }};padding-left:16px;">
                                        <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:1.5px;color:#9ca3af;text-transform:uppercase;">Bug Title</p>
                                        <p style="margin:0;font-size:15px;font-weight:600;color:#111827;line-height:1.5;">{{ $bugTitle }}</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Status badge -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:32px;">
                                <tr>
                                    <td style="background:{{ $accent['bg'] }};border:1.5px solid {{ $accent['border'] }};border-radius:10px;padding:20px 24px;">
                                        <p style="margin:0 0 8px;font-size:11px;font-weight:700;letter-spacing:1.5px;color:#9ca3af;text-transform:uppercase;">Current Status</p>
                                        <span style="display:inline-block;background:{{ $accent['badge_bg'] }};color:{{ $accent['badge_text'] }};font-size:14px;font-weight:800;padding:7px 20px;border-radius:999px;letter-spacing:0.5px;">
                                            ● {{ $statusLabel }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <!-- Progress tracker -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:32px;">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 14px;font-size:11px;font-weight:700;letter-spacing:1.5px;color:#9ca3af;text-transform:uppercase;">Progress</p>

                                        <!-- Progress bar track -->
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:12px;">
                                            <tr>
                                                <td style="background:#f3f4f6;border-radius:999px;height:8px;overflow:hidden;">
                                                    <div style="background:linear-gradient(90deg,{{ $accent['header_from'] }},{{ $accent['dot'] }});height:8px;width:{{ $progress }}%;border-radius:999px;"></div>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Step labels -->
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                @foreach($steps as $i => $step)
                                                @php
                                                    $isDone    = $currentIdx !== false && $i <= $currentIdx;
                                                    $isCurrent = $i === $currentIdx;
                                                    $dotColor  = $isDone ? $accent['dot'] : '#d1d5db';
                                                    $textColor = $isCurrent ? '#111827' : ($isDone ? '#374151' : '#9ca3af');
                                                    $weight    = $isCurrent ? '700' : '400';
                                                @endphp
                                                <td style="text-align:center;padding:0 2px;vertical-align:top;">
                                                    <p style="margin:0 0 4px;font-size:18px;color:{{ $dotColor }};">{{ $isDone ? '●' : '○' }}</p>
                                                    <p style="margin:0;font-size:10px;font-weight:{{ $weight }};color:{{ $textColor }};line-height:1.3;">{{ $step['label'] }}</p>
                                                </td>
                                                @endforeach
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:24px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $trackingUrl }}" target="_blank" style="display:inline-block;background:linear-gradient(135deg,{{ $accent['header_to'] }},{{ $accent['header_from'] }});color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 36px;border-radius:8px;letter-spacing:0.3px;">
                                            View Full Report Status →
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:13px;color:#9ca3af;line-height:1.6;text-align:center;">
                                <a href="{{ $trackingUrl }}" style="color:#6b7280;word-break:break-all;font-size:12px;">{{ $trackingUrl }}</a>
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
                                You are receiving this because you submitted a bug report.<br>
                                You will be notified on each status change until it is resolved.
                            </p>
                            <p style="margin:0;font-size:12px;color:#9ca3af;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>

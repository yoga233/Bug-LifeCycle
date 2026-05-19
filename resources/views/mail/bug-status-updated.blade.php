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
            .header-padding { padding: 28px 20px !important; }
            .step-label { font-size: 9px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f4f2f3;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">

    @php
        /*
         * Status-specific accent colors — all derived from brand palette #8a0b4e.
         * Each status gets a unique contextual color for differentiation while
         * the header gradient always stays on-brand.
         */
        $accent = match($newStatus) {
            'Assigned'    => [
                'bg'         => '#eff6ff',
                'border'     => '#bfdbfe',
                'badge_bg'   => '#dbeafe',
                'badge_text' => '#1e40af',
                'dot'        => '#3b82f6',
                'bar_from'   => '#6d0940',
                'bar_to'     => '#3b82f6',
            ],
            'In Progress' => [
                'bg'         => '#fff7ed',
                'border'     => '#fed7aa',
                'badge_bg'   => '#ffedd5',
                'badge_text' => '#9a3412',
                'dot'        => '#f97316',
                'bar_from'   => '#6d0940',
                'bar_to'     => '#f97316',
            ],
            'Testing'     => [
                'bg'         => '#f5f3ff',
                'border'     => '#ddd6fe',
                'badge_bg'   => '#ede9fe',
                'badge_text' => '#5b21b6',
                'dot'        => '#8b5cf6',
                'bar_from'   => '#6d0940',
                'bar_to'     => '#8b5cf6',
            ],
            'Resolved'    => [
                'bg'         => '#f0fdf4',
                'border'     => '#bbf7d0',
                'badge_bg'   => '#dcfce7',
                'badge_text' => '#14532d',
                'dot'        => '#22c55e',
                'bar_from'   => '#6d0940',
                'bar_to'     => '#22c55e',
            ],
            default       => [
                'bg'         => '#fdf2f8',
                'border'     => '#e8c6d8',
                'badge_bg'   => '#f5e8ef',
                'badge_text' => '#8a0b4e',
                'dot'        => '#8a0b4e',
                'bar_from'   => '#3a021f',
                'bar_to'     => '#8a0b4e',
            ],
        };

        /* Progress steps */
        $steps = [
            ['label' => 'Reported',    'pct' => 10,  'status' => 'Reported'],
            ['label' => 'Assigned',    'pct' => 30,  'status' => 'Assigned'],
            ['label' => 'In Progress', 'pct' => 55,  'status' => 'In Progress'],
            ['label' => 'QA Review',   'pct' => 80,  'status' => 'Testing'],
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
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f4f2f3;">
        <tr>
            <td align="center" style="padding:40px 16px;">

                <!-- Email card -->
                <table class="wrapper responsive-table" role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" style="max-width:600px;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 32px rgba(138,11,78,0.06),0 1px 4px rgba(0,0,0,0.04);">

                    <!-- Gradient header — always brand-colored -->
                    <tr>
                        <td class="header-padding" style="background:linear-gradient(135deg,#3a021f 0%,#8a0b4e 50%,#b01567 100%);padding:36px 40px;text-align:center;">
                            <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:3.5px;color:rgba(255,255,255,0.55);text-transform:uppercase;">Bug Lifecycle</p>
                            <h1 style="margin:4px 0 0;font-size:24px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;">Status Update</h1>
                            <p style="margin:10px 0 0;font-size:13px;color:rgba(255,255,255,0.7);">Ticket: <strong style="color:rgba(255,255,255,0.9);letter-spacing:1px;">{{ $ticket }}</strong></p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="mobile-padding" style="padding:36px 40px 28px;">

                            <!-- Greeting -->
                            <p style="margin:0 0 16px;font-size:16px;color:#1e293b;line-height:1.6;">
                                Hello <strong>{{ $guestName }}</strong>,
                            </p>
                            <p style="margin:0 0 28px;font-size:15px;color:#64748b;line-height:1.75;">
                                {{ $headline }}. Here's the latest update on your bug report:
                            </p>

                            <!-- Bug title -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:24px;">
                                <tr>
                                    <td style="border-left:3px solid #8a0b4e;padding:12px 0 12px 16px;">
                                        <p style="margin:0 0 4px;font-size:10px;font-weight:800;letter-spacing:2px;color:#94a3b8;text-transform:uppercase;">Bug Title</p>
                                        <p style="margin:0;font-size:15px;font-weight:600;color:#1e293b;line-height:1.5;">{{ $bugTitle }}</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Status badge card -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:32px;">
                                <tr>
                                    <td style="background:{{ $accent['bg'] }};border:1.5px solid {{ $accent['border'] }};border-radius:12px;padding:20px 24px;">
                                        <p style="margin:0 0 10px;font-size:10px;font-weight:800;letter-spacing:2px;color:#94a3b8;text-transform:uppercase;">Current Status</p>
                                        <span style="display:inline-block;background:{{ $accent['badge_bg'] }};color:{{ $accent['badge_text'] }};font-size:14px;font-weight:800;padding:7px 22px;border-radius:999px;letter-spacing:0.4px;">
                                            ● {{ $statusLabel }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <!-- Progress tracker -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:32px;">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 14px;font-size:10px;font-weight:800;letter-spacing:2px;color:#94a3b8;text-transform:uppercase;">Progress</p>

                                        <!-- Progress bar track -->
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:14px;">
                                            <tr>
                                                <td style="background:#f1f5f9;border-radius:999px;height:6px;overflow:hidden;">
                                                    <div style="background:linear-gradient(90deg,{{ $accent['bar_from'] }},{{ $accent['bar_to'] }});height:6px;width:{{ $progress }}%;border-radius:999px;"></div>
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
                                                    $dotColor  = $isDone ? $accent['dot'] : '#cbd5e1';
                                                    $textColor = $isCurrent ? '#1e293b' : ($isDone ? '#475569' : '#94a3b8');
                                                    $weight    = $isCurrent ? '700' : '400';
                                                @endphp
                                                <td style="text-align:center;padding:0 2px;vertical-align:top;">
                                                    <p style="margin:0 0 4px;font-size:16px;color:{{ $dotColor }};">{{ $isDone ? '●' : '○' }}</p>
                                                    <p class="step-label" style="margin:0;font-size:10px;font-weight:{{ $weight }};color:{{ $textColor }};line-height:1.3;">{{ $step['label'] }}</p>
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
                                        <!--[if mso]>
                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $trackingUrl }}" style="height:48px;v-text-anchor:middle;width:260px;" arcsize="17%" fillcolor="#8a0b4e">
                                        <w:anchorlock/>
                                        <center style="color:#ffffff;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:15px;font-weight:bold;">View Full Report Status →</center>
                                        </v:roundrect>
                                        <![endif]-->
                                        <!--[if !mso]><!-->
                                        <a href="{{ $trackingUrl }}" target="_blank" style="display:inline-block;background:linear-gradient(135deg,#8a0b4e 0%,#6d0940 100%);color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 40px;border-radius:8px;letter-spacing:0.3px;box-shadow:0 4px 14px rgba(138,11,78,0.25);">
                                            View Full Report Status →
                                        </a>
                                        <!--<![endif]-->
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:13px;color:#94a3b8;line-height:1.6;text-align:center;">
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
                                You are receiving this because you submitted a bug report.<br>
                                You will be notified on each status change until it is resolved.
                            </p>
                            <p style="margin:0;font-size:12px;color:#94a3b8;">
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

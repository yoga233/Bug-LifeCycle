<!DOCTYPE html>
<html lang="{{ $clientPortalLang ?? 'en' }}">
<head>
    @include('portal.client.landing.partials.head', [
        'title' => 'PRANALA BLMS — Report Submitted',
        'description' => 'Your report has been received. Save the ticket ID and track progress anytime.',
        'includeStyles' => false,
    ])

    @vite([
        'resources/css/app.css',
        'resources/css/portal-landing.css',
        'resources/js/app.js',
        'resources/js/portal-landing.js',
        'resources/js/portal-report-success-i18n.js',
    ])

    <style>
        /* ── Page Shell ── */
        .report-success-page {
            background: #f8fafc;
            color: var(--tx);
        }

        .report-success-main {
            padding-top: 88px;
            padding-bottom: clamp(56px, 7vw, 96px);
            min-height: 100svh;
        }

        .report-success-flow {
            max-width: 740px;
            margin: 0 auto;
            padding-top: clamp(16px, 2.4vw, 28px);
        }

        .report-success-stack {
            display: grid;
            gap: 14px;
        }

        /* ── Shared Card ── */
        .rs-card {
            position: relative;
            background: #fff;
            border: 1px solid #dde1e7;
            border-radius: 12px;
            overflow: visible;
            box-shadow:
                0 1px 2px rgba(15, 23, 42, .04),
                0 4px 12px rgba(15, 23, 42, .04);
        }

        .rs-card::before {
            content: none;
        }

        .rs-card-body {
            padding: 22px 24px;
        }

        /* ── Brand Header ── */
        .rs-card-brand {
            background: var(--p);
            border-color: var(--p);
            border-radius: 12px;
            overflow: hidden;
            box-shadow:
                0 2px 4px rgba(15, 23, 42, .06),
                0 8px 20px rgba(var(--primary-rgb), .12);
        }

        .rs-card-brand::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 3px;
            background: rgba(255, 255, 255, .22);
        }

        .rs-card-brand-inner {
            padding: clamp(22px, 3.6vw, 32px) clamp(22px, 4vw, 32px);
        }

        /* Kicker */
        .rs-card-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: var(--ff-mono);
            font-size: 10px;
            font-weight: var(--fw-medium);
            letter-spacing: var(--ls-label);
            text-transform: uppercase;
            color: rgba(255, 255, 255, .78);
            line-height: 1;
        }

        .rs-card-kicker::before {
            content: '';
            width: 14px;
            height: 1.5px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .60);
            flex-shrink: 0;
        }

        /* Page title */
        .rs-card-title {
            margin-top: 12px;
            font-family: var(--ff-sans);
            font-size: clamp(18px, 3vw, 24px);
            font-weight: var(--fw-bold);
            line-height: 1.18;
            letter-spacing: -.018em;
            color: #fff;
            max-width: 28ch;
        }

        /* Subtitle */
        .rs-card-subtitle {
            margin-top: 10px;
            font-family: var(--ff-sans);
            font-size: 14px;
            line-height: 1.72;
            color: rgba(255, 255, 255, .78);
            font-weight: var(--fw-regular);
            max-width: 56ch;
        }

        /* ── Status Icon ── */
        .rs-status-icon {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .rs-status-icon-success {
            background: rgba(16, 185, 129, .08);
            color: #047857;
            border: 1px solid rgba(16, 185, 129, .20);
        }

        .rs-status-icon-warning {
            background: rgba(251, 113, 133, .08);
            color: #be123c;
            border: 1px solid rgba(251, 113, 133, .22);
        }

        /* ── Section Title ── */
        .rs-section-title {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: var(--ff-sans);
            font-size: 13px;
            font-weight: var(--fw-semibold);
            letter-spacing: .01em;
            text-transform: uppercase;
            color: var(--tx-3);
            margin-bottom: 14px;
            line-height: 1;
        }

        .rs-section-title::before {
            content: '';
            width: 3px;
            height: 14px;
            border-radius: 2px;
            background: var(--p);
            flex-shrink: 0;
        }

        /* ── Message Title & Desc ── */
        .rs-message-title {
            margin-top: 14px;
            font-family: var(--ff-sans);
            font-size: clamp(18px, 2.8vw, 22px);
            font-weight: var(--fw-bold);
            line-height: 1.25;
            letter-spacing: -.015em;
            color: var(--tx);
        }

        .rs-message-desc {
            margin-top: 8px;
            font-family: var(--ff-sans);
            font-size: 14px;
            line-height: 1.72;
            color: var(--tx-2);
            font-weight: var(--fw-regular);
            max-width: 60ch;
        }

        /* ── Info Block ── */
        .rs-info-block {
            margin-top: 14px;
            border-radius: 8px;
            border: 1px solid rgba(59, 130, 246, .20);
            background: #f0f6ff;
            padding: 10px 14px;
            font-family: var(--ff-sans);
            font-size: 13px;
            line-height: 1.7;
            color: #1e3a8a;
        }

        /* ── Ticket Block ── */
        .rs-ticket-block {
            margin-top: 14px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #fafbfc;
            padding: 16px;
        }

        .rs-ticket-label {
            font-family: var(--ff-mono);
            font-size: 10px;
            font-weight: var(--fw-semibold);
            letter-spacing: var(--ls-label);
            text-transform: uppercase;
            color: var(--p);
            line-height: 1;
        }

        .rs-ticket-row {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .rs-ticket-number {
            font-family: var(--ff-mono);
            font-size: clamp(18px, 2.4vw, 22px);
            font-weight: var(--fw-bold);
            letter-spacing: .02em;
            color: var(--p);
            word-break: break-all;
            line-height: 1.3;
        }

        .rs-copy-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 14px;
            border-radius: 8px;
            border: 1px solid #dde1e7;
            background: #fff;
            color: var(--tx-2);
            font-family: var(--ff-sans);
            font-size: 13px;
            font-weight: var(--fw-semibold);
            cursor: pointer;
            transition: border-color .15s ease, background-color .15s ease, color .15s ease;
        }

        .rs-copy-btn:hover {
            border-color: #c1c7d0;
            background: #f8fafc;
            color: var(--tx);
        }

        .rs-copy-feedback {
            margin-top: 8px;
            font-family: var(--ff-sans);
            font-size: 12px;
            line-height: 1.6;
        }

        /* ── Email Note ── */
        .rs-email-note {
            margin-top: 14px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #fff;
            padding: 12px 14px;
            font-family: var(--ff-sans);
            font-size: 14px;
            line-height: 1.72;
            color: var(--tx-3);
        }

        .rs-email-note strong {
            color: var(--tx);
            font-weight: var(--fw-semibold);
        }

        /* ── Actions ── */
        .rs-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .rs-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 40px;
            padding: 0 20px;
            border-radius: 8px;
            font-family: var(--ff-sans);
            font-size: 13px;
            font-weight: var(--fw-semibold);
            transition: background-color .15s ease, border-color .15s ease, color .15s ease;
        }

        .rs-btn-primary {
            background: var(--p);
            color: #fff;
            border: 1px solid var(--p);
        }

        .rs-btn-primary:hover {
            background: var(--p-dk);
            border-color: var(--p-dk);
        }

        .rs-btn-secondary {
            background: #fff;
            color: var(--tx-2);
            border: 1px solid #dde1e7;
        }

        .rs-btn-secondary:hover {
            border-color: #c1c7d0;
            background: #f8fafc;
            color: var(--tx);
        }

        .rs-btn-ghost {
            background: rgba(var(--primary-rgb), .05);
            color: var(--p);
            border: 1px solid rgba(var(--primary-rgb), .18);
        }

        .rs-btn-ghost:hover {
            background: rgba(var(--primary-rgb), .09);
        }

        /* ── Task Footer ── */
        .task-footer {
            border-top: 1px solid #e2e8f0;
            background: #fff;
            padding: 18px 0;
        }

        .task-footer-inner {
            max-width: 740px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 0 var(--px);
        }

        .task-footer-copy {
            font-family: var(--ff-mono);
            font-size: 11px;
            color: var(--tx-4);
            letter-spacing: .03em;
        }

        .task-footer-links {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px 12px;
        }

        .task-footer-links a {
            font-family: var(--ff-sans);
            font-size: 12px;
            font-weight: var(--fw-medium);
            color: var(--tx-3);
            transition: color .15s ease;
        }

        .task-footer-links a:hover {
            color: var(--p);
        }

        .task-footer-links span[aria-hidden="true"] {
            color: #d1d5db;
            font-size: 12px;
            user-select: none;
        }

        /* ── Responsive ── */
        @media (min-width: 640px) {
            .rs-ticket-row {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        @media (max-width: 640px) {
            .rs-card-brand-inner,
            .rs-card-body {
                padding: 18px 16px;
            }

            .rs-card-title {
                font-size: clamp(17px, 5vw, 20px);
                max-width: none;
            }

            .rs-actions {
                grid-template-columns: 1fr;
            }

            .task-footer-inner {
                flex-direction: column;
                text-align: center;
                gap: 8px;
            }
        }

        @media (max-width: 480px) {
            .report-success-main {
                padding-top: 80px;
            }

            .rs-card-body {
                padding: 16px 14px;
            }

            .report-success-stack {
                gap: 10px;
            }

            .rs-section-title {
                font-size: 12px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .rs-copy-btn,
            .rs-btn {
                transition: none;
            }
        }
    </style>
</head>

<body class="report-success-page" data-page="client-report-success" data-i18n-ready-expected="2">
    @include('portal.client.landing.partials.skip-link')
    @include('portal.client.landing.partials.nav', [
        'landingSectionBase' => route('client.landing'),
    ])
    @include('portal.client.landing.partials.mobile-menu', [
        'landingSectionBase' => route('client.landing'),
    ])

    @php
        $ticket      = trim((string) request('ticket', ''));
        $reportEmail = session('report_email');
        $lang        = $clientPortalLang ?? 'en';
        $isId        = $lang === 'id';
    @endphp

    <main id="main" class="report-success-main">
        <section class="report-success-flow">
            <div class="wrap">
                <div class="report-success-stack">

                    {{-- ── Header Card ── --}}
                    <header class="rs-card rs-card-brand">
                        <div class="rs-card-brand-inner">
                            <span class="rs-card-kicker" data-i18n="rs_kicker">
                                {{ $isId ? 'Konfirmasi Laporan' : 'Report Confirmation' }}
                            </span>

                            <h1 class="rs-card-title" data-i18n="rs_title">
                                {{ $isId ? 'Laporan Anda sudah diterima.' : 'Your report has been received.' }}
                            </h1>

                            <p class="rs-card-subtitle" data-i18n="rs_subtitle">
                                {{ $isId
                                    ? 'Simpan ID tiket di bawah ini dan gunakan halaman pelacakan kapan saja untuk melihat status terbaru.'
                                    : 'Save the ticket ID below and use the tracking page anytime to check the latest status.'
                                }}
                            </p>
                        </div>
                    </header>

                    @if ($ticket !== '')

                        {{-- ── Status Card ── --}}
                        <section class="rs-card" aria-labelledby="status-heading">
                            <div class="rs-card-body">
                                <span class="rs-status-icon rs-status-icon-success" aria-hidden="true">
                                    <x-lucide name="check-circle" class="h-5 w-5" />
                                </span>

                                <h2 id="status-heading" class="rs-message-title" data-i18n="rs_status_title">
                                    {{ $isId ? 'Laporan berhasil dikirim' : 'Report submitted successfully' }}
                                </h2>

                                <p class="rs-message-desc" data-i18n="rs_status_desc">
                                    {{ $isId
                                        ? 'Terima kasih. Kami sudah menerima laporan Anda. Simpan nomor tiket di bawah agar Anda bisa memantau perkembangan kapan saja.'
                                        : 'Thank you. We have received your report. Save the ticket number below so you can check your report\'s progress anytime.'
                                    }}
                                </p>

                                @if (session('info'))
                                    <div class="rs-info-block" role="status">
                                        {{ session('info') }}
                                    </div>
                                @endif
                            </div>
                        </section>

                        {{-- ── Ticket Card ── --}}
                        <section class="rs-card" aria-labelledby="ticket-heading">
                            <div class="rs-card-body">
                                <h2 id="ticket-heading" class="rs-section-title" data-i18n="rs_ticket_section">
                                    {{ $isId ? 'ID Tiket Anda' : 'Your Ticket ID' }}
                                </h2>

                                <div class="rs-ticket-block">
                                    <p class="rs-ticket-label" data-i18n="rs_ticket_label">
                                        {{ $isId ? 'Nomor Tiket' : 'Ticket Number' }}
                                    </p>

                                    <div class="rs-ticket-row">
                                        <p class="rs-ticket-number">{{ $ticket }}</p>

                                        <button
                                            type="button"
                                            id="copyTicketButton"
                                            data-ticket="{{ $ticket }}"
                                            class="rs-copy-btn"
                                            aria-label="{{ $isId ? 'Salin nomor tiket' : 'Copy ticket number' }}"
                                        >
                                            <x-lucide name="copy" class="h-4 w-4" />
                                            <span id="copyTicketButtonText" data-i18n="rs_copy_btn">
                                                {{ $isId ? 'Salin Tiket' : 'Copy Ticket' }}
                                            </span>
                                        </button>
                                    </div>

                                    <p
                                        id="copyTicketFeedback"
                                        class="rs-copy-feedback hidden"
                                        aria-live="polite"
                                    ></p>
                                </div>

                                <div class="rs-email-note">
                                    @if ($reportEmail)
                                        <span data-i18n="rs_email_sent_to">
                                            {{ $isId
                                                ? 'Nomor tiket juga sudah dikirim ke'
                                                : 'The ticket number has also been sent to'
                                            }}
                                        </span>
                                        <strong>{{ $reportEmail }}</strong>.
                                    @else
                                        <span data-i18n="rs_email_sent_generic">
                                            {{ $isId
                                                ? 'Nomor tiket juga sudah dikirim ke email yang Anda masukkan saat mengirim laporan.'
                                                : 'The ticket number has also been sent to the email you provided when submitting.'
                                            }}
                                        </span>
                                    @endif
                                    <br/>
                                    <span data-i18n="rs_email_spam_note">
                                        {{ $isId
                                            ? 'Kalau tidak menemukannya, cek folder Spam atau Junk.'
                                            : 'If you cannot find it, check your Spam or Junk folder.'
                                        }}
                                    </span>
                                </div>
                            </div>
                        </section>

                        {{-- ── Actions Card ── --}}
                        <section class="rs-card" aria-label="{{ $isId ? 'Langkah selanjutnya' : 'Next steps' }}">
                            <div class="rs-card-body">
                                <h2 class="rs-section-title" data-i18n="rs_next_section">
                                    {{ $isId ? 'Langkah Selanjutnya' : 'What to Do Next' }}
                                </h2>

                                <div class="rs-actions">
                                    <a
                                        href="{{ route('client.tracking', ['ticket' => $ticket]) }}"
                                        class="rs-btn rs-btn-primary"
                                    >
                                        <x-lucide name="search" class="h-4 w-4" />
                                        <span data-i18n="rs_btn_track">
                                            {{ $isId ? 'Lacak Status Tiket' : 'Track Ticket Status' }}
                                        </span>
                                    </a>

                                    <a href="{{ route('client.landing') }}" class="rs-btn rs-btn-secondary">
                                        <x-lucide name="home" class="h-4 w-4" />
                                        <span data-i18n="rs_btn_home">
                                            {{ $isId ? 'Kembali ke Halaman Utama' : 'Back to Home' }}
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </section>

                    @else

                        {{-- ── No Ticket State ── --}}
                        <section class="rs-card" aria-labelledby="no-ticket-heading">
                            <div class="rs-card-body">
                                <span class="rs-status-icon rs-status-icon-warning" aria-hidden="true">
                                    <x-lucide name="alert-circle" class="h-5 w-5" />
                                </span>

                                <h2 id="no-ticket-heading" class="rs-message-title" data-i18n="rs_no_ticket_title">
                                    {{ $isId ? 'ID tiket tidak ditemukan' : 'Ticket ID not found' }}
                                </h2>

                                <p class="rs-message-desc" data-i18n="rs_no_ticket_desc">
                                    {{ $isId
                                        ? 'Halaman ini membutuhkan ID tiket yang valid dari pengiriman laporan. Silakan kirim laporan baru atau buka halaman pelacakan untuk mencari tiket Anda.'
                                        : 'This page needs a valid ticket ID from a submitted report. Please submit a new report or open the tracking page to find your ticket.'
                                    }}
                                </p>
                            </div>
                        </section>

                        <section class="rs-card" aria-label="{{ $isId ? 'Opsi tindakan' : 'Action options' }}">
                            <div class="rs-card-body">
                                <div class="rs-actions">
                                    <a href="{{ route('client.report') }}" class="rs-btn rs-btn-primary">
                                        <x-lucide name="plus" class="h-4 w-4" />
                                        <span data-i18n="rs_btn_new_report">
                                            {{ $isId ? 'Kirim Laporan Baru' : 'Submit New Report' }}
                                        </span>
                                    </a>

                                    <a href="{{ route('client.tracking') }}" class="rs-btn rs-btn-ghost">
                                        <x-lucide name="search" class="h-4 w-4" />
                                        <span data-i18n="rs_btn_tracking">
                                            {{ $isId ? 'Buka Pelacakan Tiket' : 'Open Ticket Tracking' }}
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </section>

                    @endif

                </div>
            </div>
        </section>
    </main>

    @include('portal.report.partials.task-footer')
</body>
</html>
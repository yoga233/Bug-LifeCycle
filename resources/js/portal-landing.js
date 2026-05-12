(function () {
'use strict';

/* ── i18n dictionary ── */
const T = {
  id: {
    /* ── FAQ ── */
    q7:'Kenapa saya tidak menerima email ID tiket atau update progres?',
    a7:'Pastikan alamat email yang Anda masukkan saat mengirim laporan sudah benar. Kalau sudah benar tapi email belum masuk, cek folder spam, junk, atau promosi. Kalau masih tidak ada, hubungi tim kami dan kami akan membantu menemukan tiket Anda.',

    skip: 'Langkau ke konten',

    /* ── FAQ categories ── */
    faq_cat_access:    'Akses',
    faq_cat_response:  'Respons',
    faq_cat_reporting: 'Pelaporan',
    faq_cat_tracking:  'Pelacakan',
    faq_cat_privacy:   'Privasi',
    faq_cat_resolution:'Penyelesaian',
    faq_cat_email:     'Email',

    /* ── Features ── */
    feat_choice_h1: 'Apa yang portal ini',
    feat_choice_h2: 'lakukan untuk Anda.',
    feat_choice_sub:'Dua fungsi utama — pelaporan dan pelacakan — yang dirancang agar prosesnya tidak menyulitkan.',

    feat_panel_1_badge:'Saat Anda Melapor',
    feat_panel_1_title:'Semua dalam satu tempat',
    feat_panel_1_desc: 'Form mengumpulkan semua yang diperlukan sejak awal — sehingga laporan Anda langsung ke tim yang tepat tanpa bolak-balik.',
    feat_r_1_t:'Form Terarah',
    feat_r_1_d:'Kolom yang memandu Anda mengisi informasi yang tepat sejak pertama kali.',
    feat_r_2_t:'Lampiran Screenshot',
    feat_r_2_d:'Sisipkan gambar langsung ke dalam laporan sebagai bukti visual.',
    feat_r_3_t:'ID Tiket Instan',
    feat_r_3_d:'Dapatkan ID pelacakan langsung setelah mengirim. Juga dikirim ke email Anda.',

    feat_panel_2_badge:'Setelah Anda Mengirim',
    feat_panel_2_title:'Selalu tahu posisi laporan Anda',
    feat_panel_2_desc: 'Setelah dikirim, Anda tidak perlu menanyakan ke siapapun. Lacak tiket sendiri, kapan saja.',
    feat_t_1_t:'Status Langsung',
    feat_t_1_d:'Lihat posisi tiket Anda saat ini — dari masuk hingga selesai.',
    feat_t_2_t:'Notifikasi Email',
    feat_t_2_d:'Kami memberitahu Anda setiap ada perubahan status.',
    feat_t_3_t:'Riwayat Lengkap',
    feat_t_3_d:'Setiap update tersimpan sehingga Anda bisa melihat seluruh timeline kapan saja.',

    feat_note:'Semua ini dirancang agar Anda tahu persis apa yang terjadi dengan laporan Anda — tanpa harus menanyakan ke siapapun.',

    /* ── Nav ── */
    nav_home:  'Home',
    nav_why:   'Kenapa Portal Ini',
    nav_hiw:   'Cara Kerja',
    nav_features: 'Fitur',
    nav_faq:   'FAQ',
    nav_track: 'Lacak Tiket',
    nav_report:'Kirim Laporan',

    /* ── Hero ── */
    hero_pill: 'PRANALA BLMS · Bug Lifecycle Management System',
    hero_h1a:  'Laporkan sekali.',
    hero_h1b:  'Pantau',
    hero_h1c:  'sampai selesai.',
    hero_p:    'Kirim laporan terstruktur ke tim engineering kami dan pantau setiap perkembangannya secara real-time — tidak ada lagi laporan yang hilang tanpa kabar.',

    f_structured: 'Form Terstruktur',
    f_realtime:   'Status Real-time',
    f_audit:      'Riwayat Progres',

    cta_report: 'Kirim Laporan',
    cta_track:  'Lacak Tiket Saya',

    /* ── Marquee ── */
    mq_structured:     'Form Terstruktur',
    mq_structured_sub: 'Semua detail tercatat sejak awal',
    mq_tracking:       'Status Real-time',
    mq_tracking_sub:   'Lihat posisi laporan Anda kapan saja',
    mq_notify:         'Update via Email',
    mq_notify_sub:     'Notifikasi setiap perubahan status',
    mq_triage_new:     'Penanganan Prioritas',
    mq_triage_new_sub: 'Masalah kritis dieskalasi lebih cepat',
    mq_audit_new:      'Riwayat Progres',
    mq_audit_new_sub:  'Setiap tindakan tercatat dan bisa ditelusuri',
    mq_verified:       'Resolusi Terverifikasi',
    mq_verified_sub:   'Ditutup setelah perbaikan dikonfirmasi',

    /* ── Problems ── */
    s_problem: 'Kenapa Portal Ini',
    prob_h1:   'Pelaporan bug yang',
    prob_h2:   'benar-benar tertangani.',
    prob_p:    'Portal ini ada agar setiap masalah yang Anda laporkan mendapat tiket yang jelas, ditangani sesuai urgensi, dan bisa Anda pantau kapan saja.',
    p1_t: 'Setiap laporan dapat tiket',
    p1_b: 'Tidak ada lagi laporan yang tertumpuk di grup chat. Begitu Anda kirim, laporan langsung jadi tiket dengan ID unik yang bisa dilacak kapan saja.',
    p2_t: 'Konteks lengkap, solusi lebih cepat',
    p2_b: 'Form kami membantu Anda melengkapi detail yang tepat sejak awal — screenshot, langkah, dan kondisi yang dialami — sehingga masalah bisa diselesaikan lebih cepat.',
    p3_t: 'Selalu tahu perkembangan terbaru',
    p3_b: 'Pantau status tiket Anda secara real-time — dari saat laporan diterima hingga saat perbaikan dikonfirmasi selesai. Tidak perlu menanyakan ke siapapun.',

    /* ── How It Works ── */
    s_hiw:          'Cara Kerja',
    hiw_choice_h1:  'Dua hal yang bisa',
    hiw_choice_h2:  'Anda lakukan di sini.',
    hiw_choice_sub: 'Pilih sesuai kebutuhan Anda — kirim laporan baru atau cek status tiket yang sudah ada.',
    hiw_or:         'atau',

    hiw_path_1_badge:'Pilihan 1',
    hiw_path_1_title:'Laporkan Masalah',
    hiw_path_1_desc: 'Jika Anda menemukan masalah dan butuh tim kami untuk menginvestigasi dan memperbaikinya.',
    hiw_r_1_t: 'Buka form laporan',
    hiw_r_1_d: 'Klik tombol "Kirim Laporan" untuk mulai.',
    hiw_r_2_t: 'Jelaskan masalahnya',
    hiw_r_2_d: 'Isi apa yang terjadi, kapan, dan tambahkan screenshot kalau ada.',
    hiw_r_3_t: 'Simpan ID tiket Anda',
    hiw_r_3_d: 'Submit dan simpan ID yang Anda terima. Kami juga akan mengirimnya ke email Anda.',
    hiw_r_email_note: 'ID tiket dikirim otomatis ke email Anda segera setelah laporan masuk.',

    hiw_path_2_badge:'Pilihan 2',
    hiw_path_2_title:'Cek Status Tiket',
    hiw_path_2_desc: 'Jika Anda sudah punya ID tiket dan ingin melihat perkembangan terbaru laporan Anda.',
    hiw_t_1_t: 'Buka halaman pelacakan',
    hiw_t_1_d: 'Klik tombol "Lacak Tiket Saya" untuk membuka halaman tracking.',
    hiw_t_2_t: 'Masukkan ID tiket',
    hiw_t_2_d: 'Gunakan ID yang Anda terima setelah mengirim laporan.',
    hiw_t_3_t: 'Lihat status dan update',
    hiw_t_3_d: 'Pantau progres dan semua perubahan status hingga masalah selesai.',
    hiw_t_email_note: 'Setiap perubahan status juga dikirim langsung ke email Anda.',

    hiw_choice_note: 'Tip: Setelah mengirim laporan, simpan ID tiket Anda. ID ini dibutuhkan untuk melihat perkembangan laporan.',

    /* ── Features section label ── */
    s_features: 'Fitur',
    feat_h1:    'Apa yang portal ini',
    feat_h2:    'lakukan untuk Anda.',
    feat_p:     'Dua fungsi utama — pelaporan dan pelacakan — yang dirancang agar prosesnya tidak menyulitkan.',

    f1t: 'Form Terarah',
    f1d: 'Kolom yang memandu Anda mengisi informasi yang tepat — sehingga laporan bisa langsung diproses tanpa bolak-balik.',
    f2t: 'Lampiran Screenshot',
    f2d: 'Sisipkan gambar langsung ke dalam laporan. Tim kami melihat persis apa yang Anda lihat.',
    f3t: 'Status Real-time',
    f3d: 'Pantau posisi tiket kapan saja — sedang diproses, menunggu pengujian, atau sudah selesai.',
    f4t: 'Notifikasi Email',
    f4d: 'Setiap perubahan status langsung masuk ke email Anda. Tidak perlu follow-up manual.',
    f5t: 'Penanganan Prioritas',
    f5d: 'Masalah dengan dampak tertinggi ditangani lebih dahulu. Kritis selalu naik ke atas antrian.',
    f6t: 'Riwayat Progres',
    f6d: 'Setiap tindakan tercatat — siapa yang membuka, mengomentari, dan menyelesaikan. Semua bisa ditelusuri.',

    /* ── FAQ ── */
    faq_h1:  'Pertanyaan yang',
    faq_h2:  'sering ditanyakan.',
    faq_sub: 'Kalau ada yang tidak jelas, jawabannya mungkin ada di sini.',
    q1: 'Siapa yang bisa menggunakan portal ini?',
    a1: 'Semua klien PRANALA dengan proyek aktif. Kalau tidak yakin apakah proyek Anda terdaftar, tanyakan ke project manager Anda — biasanya satu pesan sudah cukup.',
    q2: 'Seberapa cepat tim akan merespons?',
    a2: 'Kami merespons setiap laporan selama jam kerja. Masalah kritis langsung dieskalasi. Anda akan menerima konfirmasi email begitu tiket diterima — sehingga Anda tahu laporan sudah masuk antrian.',
    q3: 'Apa saja yang perlu saya isi di laporan?',
    a3: 'Form akan memandu Anda — tapi intinya: apa yang terjadi, kapan, apa yang seharusnya muncul, dan screenshot kalau ada. Semakin spesifik, semakin cepat kami bisa memperbaikinya.',
    q4: 'Bisakah saya memantau laporan setelah dikirim?',
    a4: 'Bisa. Anda akan menerima ID tiket setelah mengirim. Gunakan halaman pelacakan untuk melihat status real-time, siapa yang menangani, dan update terbaru. Kami juga mengirim notifikasi email setiap ada perubahan.',
    q5: 'Apakah laporan saya bersifat rahasia?',
    a5: 'Ya. Laporan Anda hanya bisa dilihat oleh organisasi Anda dan tim engineering PRANALA yang ditugaskan ke proyek Anda. Tidak ada yang dibagikan ke pihak lain.',
    q6: 'Apa yang terjadi setelah masalah saya diperbaiki?',
    a6: 'Anda akan menerima email ringkasan — apa yang diperbaiki dan bagaimana pengujiannya. Tiket tetap bisa diakses di riwayat Anda. Kalau masalah muncul lagi, Anda bisa membukanya kembali dari halaman pelacakan.',

    /* ── CTA section ── */
    cta_pill:     'Siap kapanpun Anda siap',
    cta_h1:       'Setiap masalah yang dilaporkan adalah',
    cta_h2:       'sistem yang menjadi lebih baik.',
    cta_p:        'Kirim laporan sekarang dan tim engineering kami akan menanganinya dengan transparan — dari laporan masuk hingga perbaikan dikonfirmasi.',
    cta_track_ex: 'Lacak Tiket yang Ada',
    cta_note:     'Setiap laporan ditindaklanjuti selama jam kerja operasional',

    /* ── Footer ── */
    foot_tagline: 'Portal pelaporan masalah dan pelacakan tiket untuk klien PRANALA.',
    foot_nav_h:   'Portal',
    foot_act_h:   'Aksi',
    foot_sup_h:   'Dukungan',
    foot_report:  'Kirim Laporan',
    foot_track:   'Lacak Tiket',
    foot_signin:  'Login Internal',
    foot_resp:    'Tim tersedia selama jam kerja',
    foot_resp_sub:'Setiap laporan ditinjau dan ditangani sesuai urgensi.',
    foot_rights:  'Hak cipta dilindungi.',
    foot_privacy: 'Kebijakan Privasi',
    foot_terms:   'Ketentuan Layanan',

    /* ── Aria ── */
    aria_nav:   'Navigasi utama',
    aria_close: 'Tutup menu',
    aria_open:  'Buka menu',
  },

  en: {
    /* ── FAQ ── */
    q7:'Why did I not receive my ticket ID email or progress updates?',
    a7:'Check that the email you entered was correct. Then check your spam or promotions folder. If it is still missing, contact us and we will help you locate your ticket.',

    skip: 'Skip to content',

    /* ── FAQ categories ── */
    faq_cat_access:    'Access',
    faq_cat_response:  'Response',
    faq_cat_reporting: 'Reporting',
    faq_cat_tracking:  'Tracking',
    faq_cat_privacy:   'Privacy',
    faq_cat_resolution:'Resolution',
    faq_cat_email:     'Email',

    /* ── Features ── */
    feat_choice_h1: 'What this portal',
    feat_choice_h2: 'does for you.',
    feat_choice_sub:'Two core functions — reporting and tracking — designed to make the whole process less frustrating.',

    feat_panel_1_badge:'When You Report',
    feat_panel_1_title:'Everything in one place',
    feat_panel_1_desc: 'The form collects everything needed upfront — so your report goes straight to the right team without back-and-forth.',
    feat_r_1_t:'Guided Form',
    feat_r_1_d:'Clear fields that walk you through exactly what to include from the start.',
    feat_r_2_t:'Screenshot Attachment',
    feat_r_2_d:'Attach images directly to your report as visual evidence.',
    feat_r_3_t:'Instant Ticket ID',
    feat_r_3_d:'Get your tracking ID right after submitting. Also sent to your email.',

    feat_panel_2_badge:'After You Submit',
    feat_panel_2_title:'Always know where things stand',
    feat_panel_2_desc: 'Once submitted, you do not have to ask anyone. Track your ticket yourself, anytime.',
    feat_t_1_t:'Live Status',
    feat_t_1_d:'See exactly where your ticket is right now — from intake to resolution.',
    feat_t_2_t:'Email Alerts',
    feat_t_2_d:'We notify you every time something changes on your ticket.',
    feat_t_3_t:'Full History',
    feat_t_3_d:'Every update is saved so you can review the complete timeline anytime.',

    feat_note:'All of this is designed so you always know what is happening with your report — without having to ask anyone.',

    /* ── Nav ── */
    nav_home:     'Home',
    nav_why:      'Why This Portal',
    nav_hiw:      'How It Works',
    nav_features: 'Features',
    nav_faq:      'FAQ',
    nav_track:    'Track Ticket',
    nav_report:   'Submit Report',

    /* ── Hero ── */
    hero_pill: 'PRANALA BLMS · Bug Lifecycle Management System',
    hero_h1a:  'Submit once.',
    hero_h1b:  'Track',
    hero_h1c:  'until resolved.',
    hero_p:    'Send a structured report to our engineering team and follow every update in real-time — no more wondering what happened to your report.',

    f_structured: 'Structured Form',
    f_realtime:   'Real-time Status',
    f_audit:      'Progress History',

    cta_report: 'Submit Report',
    cta_track:  'Track My Ticket',

    /* ── Marquee ── */
    mq_structured:     'Structured Form',
    mq_structured_sub: 'Every detail captured from the start',
    mq_tracking:       'Real-time Status',
    mq_tracking_sub:   'See exactly where your report stands',
    mq_notify:         'Email Updates',
    mq_notify_sub:     'Notified on every status change',
    mq_triage_new:     'Priority Handling',
    mq_triage_new_sub: 'Critical issues escalated immediately',
    mq_audit_new:      'Progress History',
    mq_audit_new_sub:  'Every action logged and traceable',
    mq_verified:       'Verified Resolution',
    mq_verified_sub:   'Closed only after the fix is confirmed',

    /* ── Problems ── */
    s_problem: 'Why This Portal',
    prob_h1:   'Bug reporting that',
    prob_h2:   'actually gets handled.',
    prob_p:    'This portal exists so every issue you report gets a proper ticket, handled in the right order, with progress you can see at any time.',
    p1_t: 'Every report gets a ticket',
    p1_b: 'No more messages getting buried in group chats. The moment you submit, your report becomes a tracked ticket with a unique ID.',
    p2_t: 'More context, faster resolution',
    p2_b: 'Our guided form collects the right details upfront — screenshots, steps, and what you experienced — so your issue gets resolved faster.',
    p3_t: 'Always know what is happening',
    p3_b: 'Track your ticket status in real-time — from the moment it is received to the moment the fix is confirmed. No need to ask anyone.',

    /* ── How It Works ── */
    s_hiw:          'How It Works',
    hiw_choice_h1:  'Two things you can',
    hiw_choice_h2:  'do here.',
    hiw_choice_sub: 'Choose what applies to you — submit a new report or check the status of an existing ticket.',
    hiw_or:         'or',

    hiw_path_1_badge:'Option 1',
    hiw_path_1_title:'Report a Problem',
    hiw_path_1_desc: 'If you found an issue and need our team to investigate and fix it.',
    hiw_r_1_t: 'Open the form',
    hiw_r_1_d: 'Click the "Submit Report" button to get started.',
    hiw_r_2_t: 'Describe the issue',
    hiw_r_2_d: 'Fill in what happened, when, and add a screenshot if you have one.',
    hiw_r_3_t: 'Save your ticket ID',
    hiw_r_3_d: 'Submit and keep the ID you receive. We will also email it to you.',
    hiw_r_email_note: 'Your ticket ID is sent automatically to your email as soon as the report is received.',

    hiw_path_2_badge:'Option 2',
    hiw_path_2_title:'Check Ticket Status',
    hiw_path_2_desc: 'If you already have a ticket ID and want to see the latest updates on your report.',
    hiw_t_1_t: 'Open the tracking page',
    hiw_t_1_d: 'Click the "Track My Ticket" button to open the tracking page.',
    hiw_t_2_t: 'Enter your ticket ID',
    hiw_t_2_d: 'Use the ID you received after submitting your report.',
    hiw_t_3_t: 'View status and updates',
    hiw_t_3_d: 'Follow progress and all status changes until your issue is resolved.',
    hiw_t_email_note: 'Every status change is also sent directly to your email.',

    hiw_choice_note: 'Tip: After submitting, save your ticket ID. You will need it to check your report\'s progress.',

    /* ── Features section label ── */
    s_features: 'Features',
    feat_h1:    'What this portal',
    feat_h2:    'does for you.',
    feat_p:     'Two core functions — reporting and tracking — designed to make the whole process less frustrating.',

    f1t: 'Guided Form',
    f1d: 'Clear fields that walk you through what to include — so your report can be processed without back-and-forth.',
    f2t: 'Screenshot Attachment',
    f2d: 'Attach images directly to your report. Our team sees exactly what you see.',
    f3t: 'Real-time Status',
    f3d: 'Track your ticket anytime — whether it is being worked on, pending review, or already resolved.',
    f4t: 'Email Notifications',
    f4d: 'Every status change goes straight to your email. No manual follow-ups needed.',
    f5t: 'Priority Handling',
    f5d: 'Issues with the highest impact are handled first. Critical reports are always escalated immediately.',
    f6t: 'Progress History',
    f6d: 'Every action is logged — who opened it, commented, and resolved it. Everything is traceable.',

    /* ── FAQ ── */
    faq_h1:  'Common',
    faq_h2:  'questions.',
    faq_sub: 'If something is unclear, the answer is probably here.',
    q1: 'Who can use this portal?',
    a1: 'Any PRANALA client with an active project. If you are not sure whether your project is registered, ask your project manager — it usually takes one message to confirm.',
    q2: 'How fast will the team respond?',
    a2: 'We respond to every report during business hours. Critical issues are escalated immediately. You will receive an email confirmation as soon as your ticket is received — so you know it is in the queue.',
    q3: 'What do I need to fill in the report?',
    a3: 'The form will guide you through it — but the key things are: what happened, when it happened, what you expected instead, and a screenshot if you have one. The more specific, the faster we can fix it.',
    q4: 'Can I check my report after submitting?',
    a4: 'Yes. You will receive a ticket ID after submitting. Use the tracking page to see real-time status, who is working on it, and any updates. We also send email notifications on every status change.',
    q5: 'Is my report kept private?',
    a5: 'Yes. Your report is only visible to your organization and the PRANALA engineering team assigned to your project. Nothing is shared with anyone else.',
    q6: 'What happens when my issue is fixed?',
    a6: 'You will receive a closure email with a summary of what was fixed and how it was tested. The ticket stays accessible in your history. If the issue comes back, you can reopen it directly from the tracking page.',

    /* ── CTA section ── */
    cta_pill:     'Ready when you are',
    cta_h1:       'Every issue reported is a',
    cta_h2:       'system made better.',
    cta_p:        'Submit your report now and our engineering team will handle it transparently — from the moment it arrives to the moment it is confirmed resolved.',
    cta_track_ex: 'Track Existing Ticket',
    cta_note:     'Every report is actioned during operational business hours',

    /* ── Footer ── */
    foot_tagline: 'Issue reporting and ticket tracking portal for PRANALA clients.',
    foot_nav_h:   'Portal',
    foot_act_h:   'Actions',
    foot_sup_h:   'Support',
    foot_report:  'Submit Report',
    foot_track:   'Track Ticket',
    foot_signin:  'Internal Login',
    foot_resp:    'Team available during business hours',
    foot_resp_sub:'Every report is reviewed and handled based on urgency.',
    foot_rights:  'All rights reserved.',
    foot_privacy: 'Privacy Policy',
    foot_terms:   'Terms of Service',

    /* ── Aria ── */
    aria_nav:   'Main navigation',
    aria_close: 'Close menu',
    aria_open:  'Open menu',
  }
};

const pageType = document.body?.dataset?.page || '';
const LANG_STORAGE_KEY = 'client-portal-language';
const INITIAL_SERVER_LANG = (typeof window.__clientInitialLang === 'string' ? window.__clientInitialLang : '').trim();
const INITIAL_BOOTSTRAP_LANG = (typeof window.__clientLandingLang === 'string' ? window.__clientLandingLang : '').trim();

const isKnownLang = (value) => Object.prototype.hasOwnProperty.call(T, value);

const getPersistedLang = () => {
  try {
    const storedLang = window.localStorage.getItem(LANG_STORAGE_KEY);
    return isKnownLang(storedLang) ? storedLang : null;
  } catch (_) {
    return null;
  }
};

const persistLang = (value) => {
  if (!isKnownLang(value)) return;
  try {
    window.localStorage.setItem(LANG_STORAGE_KEY, value);
  } catch (_) {
    // ignore storage issues
  }
};

const syncLangToServer = async (value) => {
  if (!isKnownLang(value)) return;
  try {
    const res = await fetch('/portal/language', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ lang: value }),
    });
    if (!res.ok) return;
    const data = await res.json().catch(() => null);
    if (data?.success) return;
  } catch (_) {
    // ignore network/server issues
  }
};

let lang = INITIAL_SERVER_LANG || INITIAL_BOOTSTRAP_LANG || getPersistedLang() || 'en';
if (!isKnownLang(lang)) lang = 'en';

window.setLang = function (l, options = {}) {
  const silentSync = options?.silentSync === true;
  lang = T[l] ? l : 'en';
  persistLang(lang);

  if (!silentSync) {
    syncLangToServer(lang);
  }

  const t = T[lang];

  document.documentElement.lang = lang === 'id' ? 'id' : 'en';
  document.documentElement.setAttribute('data-client-lang', lang);

  if (pageType === 'client-report') {
    document.title = lang === 'id'
      ? 'PRANALA BLMS — Form Laporan Bug'
      : 'PRANALA BLMS — Bug Report Form';
  } else if (pageType === '' || pageType === 'client-landing') {
    document.title = lang === 'id'
      ? 'PRANALA BLMS — Sistem Pelaporan Bug'
      : 'PRANALA BLMS — Bug Lifecycle Management System';
  }

  const navEl = document.querySelector('[aria-label="Navigasi utama"], [aria-label="Main navigation"]');
  if (navEl) {
    navEl.setAttribute('aria-label', t.aria_nav);
  }

  document.querySelectorAll('[data-i18n]').forEach((el) => {
    const key = el.dataset.i18n;
    if (t[key] !== undefined) {
      el.textContent = t[key];
    }
  });

  ['btn-id', 'btn-en'].forEach((id) => {
    const btn = document.getElementById(id);
    if (!btn) return;
    const active = btn.id.endsWith(lang);
    btn.classList.toggle('active', active);
    btn.setAttribute('aria-pressed', String(active));
  });

  ['mob-btn-id', 'mob-btn-en'].forEach((id) => {
    const btn = document.getElementById(id);
    if (!btn) return;
    const active = btn.id.endsWith(lang);
    btn.classList.toggle('active', active);
    btn.setAttribute('aria-pressed', String(active));
  });

  const hamBtn = document.getElementById('ham');
  const mobCloseBtn = document.getElementById('mob-x');

  if (hamBtn) {
    const isExpanded = hamBtn.getAttribute('aria-expanded') === 'true';
    hamBtn.setAttribute('aria-label', isExpanded ? t.aria_close : t.aria_open);
  }

  if (mobCloseBtn) {
    mobCloseBtn.setAttribute('aria-label', t.aria_close);
  }

  window.__clientLandingLang = lang;
  window.dispatchEvent(new CustomEvent('client-lang-changed', {
    detail: { lang },
  }));

  if (typeof window.__markClientI18nReady === 'function') {
    window.__markClientI18nReady('landing');
  }
};

window.getClientLandingLang = () => lang;

/* ── Nav scroll ── */
const nav = document.getElementById('nav');
window.addEventListener('scroll', () => {
  if (nav) {
    nav.classList.toggle('scrolled', window.scrollY > 24);
  }
}, { passive: true });

/* ── Mobile menu ── */
const ham = document.getElementById('ham');
const mob = document.getElementById('mob');
const mobX = document.getElementById('mob-x');

const openM = () => {
  if (!mob || !ham) return;
  mob.classList.add('open');
  document.body.style.overflow = 'hidden';
  ham.setAttribute('aria-expanded', 'true');
  ham.setAttribute('aria-label', T[lang]?.aria_close || 'Close menu');
  if (mobX) mobX.focus();
};

const closeM = (restoreFocus = true) => {
  if (!mob || !ham) return;
  mob.classList.remove('open');
  document.body.style.overflow = '';
  ham.setAttribute('aria-expanded', 'false');
  ham.setAttribute('aria-label', T[lang]?.aria_open || 'Open menu');
  if (restoreFocus) ham.focus();
};

if (ham) ham.addEventListener('click', openM);
if (mobX) mobX.addEventListener('click', () => closeM());

if (mob) {
  mob.addEventListener('click', (e) => {
    if (e.target === mob) closeM(false);
  });

  mob.querySelectorAll('a').forEach((a) => {
    a.addEventListener('click', () => closeM(false));
  });
}

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && mob?.classList.contains('open')) {
    closeM();
  }
});

/* ── Image lazy fade ── */
document.querySelectorAll('img').forEach((img) => {
  if (img.complete) {
    img.classList.add('ld');
  } else {
    img.addEventListener('load', () => img.classList.add('ld'), { once: true });
  }
});

/* ── Reveal ── */
const rm = window.matchMedia('(prefers-reduced-motion:reduce)').matches;

if (!rm) {
  const io = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('vis');
        io.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.06,
    rootMargin: '0px 0px -20px 0px'
  });

  document.querySelectorAll('.reveal').forEach((el) => io.observe(el));
} else {
  document.querySelectorAll('.reveal').forEach((el) => el.classList.add('vis'));
}

/* ── FAQ accordion ── */
document.querySelectorAll('.faq-btn').forEach((btn) => {
  btn.addEventListener('click', () => {
    const isOpen = btn.classList.contains('open');

    document.querySelectorAll('.faq-btn').forEach((b) => {
      b.classList.remove('open');
      b.setAttribute('aria-expanded', 'false');
      const body = document.getElementById(b.getAttribute('aria-controls'));
      if (body) body.classList.remove('open');
    });

    if (!isOpen) {
      btn.classList.add('open');
      btn.setAttribute('aria-expanded', 'true');
      const body = document.getElementById(btn.getAttribute('aria-controls'));
      if (body) body.classList.add('open');
    }
  });
});

/* ── Active nav link on scroll ── */
const navSectionMap = [
  { id: 'hero',     key: 'hero'     },
  { id: 'problems', key: 'problems' },
  { id: 'hiw',      key: 'hiw'      },
  { id: 'features', key: 'features' },
  { id: 'faq',      key: 'faq'      },
];

const navAnchors = document.querySelectorAll('.nav-links a, .mob-nav a');
const isLandingPage = pageType === '' || pageType === 'client-landing';

function clearActiveNavLinks() {
  navAnchors.forEach((link) => {
    link.classList.remove('is-active');
    link.removeAttribute('aria-current');
  });
}

let navTick = false;
function updateActiveLink() {
  if (!isLandingPage) {
    clearActiveNavLinks();
    return;
  }

  if (navTick) return;
  navTick = true;

  requestAnimationFrame(() => {
    let current = null;

    navSectionMap.forEach((item) => {
      const section = document.getElementById(item.id);
      if (!section) return;
      const rect = section.getBoundingClientRect();
      if (rect.top <= 120) {
        current = item.key;
      }
    });

    navAnchors.forEach((link) => {
      const href = link.getAttribute('href') || '';
      const isActive = current ? href.includes(`#${current}`) : false;
      link.classList.toggle('is-active', isActive);
      if (isActive) {
        link.setAttribute('aria-current', 'location');
      } else {
        link.removeAttribute('aria-current');
      }
    });

    navTick = false;
  });
}

if (isLandingPage) {
  window.addEventListener('scroll', updateActiveLink, { passive: true });
  window.addEventListener('resize', updateActiveLink, { passive: true });
  window.addEventListener('load',   updateActiveLink);
} else {
  clearActiveNavLinks();
}

/* ── Initialise ── */
updateActiveLink();
window.setLang(lang, { silentSync: true });

})();
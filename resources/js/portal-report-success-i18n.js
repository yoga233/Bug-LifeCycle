(() => {
  'use strict';

  const body = document.body;
  if (!body || body.dataset.page !== 'client-report-success') {
    return;
  }

  const successI18n = {
    en: {
      /* ── Page ── */
      rs_page_title: 'PRANALA BLMS — Report Submitted',

      /* ── Header card ── */
      rs_kicker:   'Report Confirmation',
      rs_title:    'Your report has been received.',
      rs_subtitle: 'Save the ticket ID below and use the tracking page anytime to check the latest status.',

      /* ── Status card ── */
      rs_status_title: 'Report submitted successfully',
      rs_status_desc:  'Thank you. We have received your report. Save the ticket number below so you can check your report\'s progress anytime.',

      /* ── Ticket card ── */
      rs_ticket_section: 'Your Ticket ID',
      rs_ticket_label:   'Ticket Number',
      rs_copy_btn:       'Copy Ticket',
      rs_copy_copied:    'Copied',
      rs_copy_success:   'Ticket number copied to clipboard.',
      rs_copy_no_ticket: 'Ticket number not found. Please refresh the page.',
      rs_copy_fail:      'Could not copy automatically. Please copy the ticket number manually.',

      /* ── Email note ── */
      rs_email_sent_to:      'The ticket number has also been sent to',
      rs_email_sent_generic: 'The ticket number has also been sent to the email you provided when submitting.',
      rs_email_spam_note:    'If you cannot find it, check your Spam or Junk folder.',

      /* ── Actions card ── */
      rs_next_section:   'What to Do Next',
      rs_btn_track:      'Track Ticket Status',
      rs_btn_home:       'Back to Home',

      /* ── No ticket state ── */
      rs_no_ticket_title: 'Ticket ID not found',
      rs_no_ticket_desc:  'This page needs a valid ticket ID from a submitted report. Please submit a new report or open the tracking page to find your ticket.',
      rs_btn_new_report:  'Submit New Report',
      rs_btn_tracking:    'Open Ticket Tracking',
    },

    id: {
      /* ── Page ── */
      rs_page_title: 'PRANALA BLMS — Laporan Terkirim',

      /* ── Header card ── */
      rs_kicker:   'Konfirmasi Laporan',
      rs_title:    'Laporan Anda sudah diterima.',
      rs_subtitle: 'Simpan ID tiket di bawah ini dan gunakan halaman pelacakan kapan saja untuk melihat status terbaru.',

      /* ── Status card ── */
      rs_status_title: 'Laporan berhasil dikirim',
      rs_status_desc:  'Terima kasih. Kami sudah menerima laporan Anda. Simpan nomor tiket di bawah agar Anda bisa memantau perkembangan kapan saja.',

      /* ── Ticket card ── */
      rs_ticket_section: 'ID Tiket Anda',
      rs_ticket_label:   'Nomor Tiket',
      rs_copy_btn:       'Salin Tiket',
      rs_copy_copied:    'Tersalin',
      rs_copy_success:   'Nomor tiket berhasil disalin.',
      rs_copy_no_ticket: 'Nomor tiket tidak ditemukan. Coba refresh halaman.',
      rs_copy_fail:      'Gagal menyalin otomatis. Silakan salin nomor tiket secara manual.',

      /* ── Email note ── */
      rs_email_sent_to:      'Nomor tiket juga sudah dikirim ke',
      rs_email_sent_generic: 'Nomor tiket juga sudah dikirim ke email yang Anda masukkan saat mengirim laporan.',
      rs_email_spam_note:    'Kalau tidak menemukannya, cek folder Spam atau Junk.',

      /* ── Actions card ── */
      rs_next_section:   'Langkah Selanjutnya',
      rs_btn_track:      'Lacak Status Tiket',
      rs_btn_home:       'Kembali ke Halaman Utama',

      /* ── No ticket state ── */
      rs_no_ticket_title: 'ID tiket tidak ditemukan',
      rs_no_ticket_desc:  'Halaman ini membutuhkan ID tiket yang valid dari pengiriman laporan. Silakan kirim laporan baru atau buka halaman pelacakan untuk mencari tiket Anda.',
      rs_btn_new_report:  'Kirim Laporan Baru',
      rs_btn_tracking:    'Buka Pelacakan Tiket',
    },
  };

  const defaultLang = 'en';
  const LANG_STORAGE_KEY = 'client-portal-language';
  let activeLang = defaultLang;

  const getPersistedLang = () => {
    try {
      const storedLang = window.localStorage.getItem(LANG_STORAGE_KEY);
      return successI18n[storedLang] ? storedLang : null;
    } catch (_) {
      return null;
    }
  };

  const resolveCurrentLang = () => {
    const persistedLang = getPersistedLang();
    if (persistedLang) {
      return persistedLang;
    }
    if (typeof window.__clientInitialLang === 'string') {
      return window.__clientInitialLang;
    }
    if (typeof window.getClientLandingLang === 'function') {
      return window.getClientLandingLang();
    }
    if (typeof window.__clientLandingLang === 'string') {
      return window.__clientLandingLang;
    }
    return defaultLang;
  };

  const getDictionary = (lang) => successI18n[lang] || successI18n[defaultLang] || {};

  const getText = (key, fallback = '') => {
    const current = getDictionary(activeLang);
    const fallbackDict = getDictionary(defaultLang);
    return current[key] ?? fallbackDict[key] ?? fallback;
  };

  /* ── Copy button state ── */
  const getCopyLabels = () => ({
    copy:       getText('rs_copy_btn', 'Copy Ticket'),
    copied:     getText('rs_copy_copied', 'Copied'),
    successMsg: getText('rs_copy_success', 'Ticket number copied to clipboard.'),
    noTicket:   getText('rs_copy_no_ticket', 'Ticket number not found.'),
    failMsg:    getText('rs_copy_fail', 'Could not copy automatically.'),
  });

  let copyLabels = getCopyLabels();

  /* ── Apply language ── */
  const applySuccessLanguage = (lang) => {
    activeLang = successI18n[lang] ? lang : defaultLang;
    const dict = getDictionary(activeLang);

    /* Page title */
    document.title = getText('rs_page_title', document.title);

    /* All data-i18n elements (shared with landing JS) */
    document.querySelectorAll('[data-i18n]').forEach((el) => {
      const key = el.dataset.i18n;
      if (key && dict[key] !== undefined) {
        el.textContent = dict[key];
      }
    });

    /* Update copy button labels */
    copyLabels = getCopyLabels();

    const copyBtnText = document.getElementById('copyTicketButtonText');
    if (copyBtnText) {
      copyBtnText.textContent = copyLabels.copy;
    }

    const copyBtn = document.getElementById('copyTicketButton');
    if (copyBtn) {
      copyBtn.setAttribute(
        'aria-label',
        activeLang === 'id' ? 'Salin nomor tiket' : 'Copy ticket number'
      );
    }

    /* Reset feedback on lang change */
    const feedback = document.getElementById('copyTicketFeedback');
    if (feedback && !feedback.classList.contains('hidden')) {
      feedback.classList.add('hidden');
    }

    if (typeof window.__markClientI18nReady === 'function') {
      window.__markClientI18nReady('report-success');
    }
  };

  /* ── Copy functionality ── */
  const initCopyButton = () => {
    const copyButton   = document.getElementById('copyTicketButton');
    const copyBtnText  = document.getElementById('copyTicketButtonText');
    const copyFeedback = document.getElementById('copyTicketFeedback');

    if (!copyButton || !copyBtnText || !copyFeedback) {
      return;
    }

    const ticket = copyButton.dataset.ticket || '';

    const setFeedback = (message, tone) => {
      copyFeedback.textContent = message;
      copyFeedback.classList.remove(
        'hidden',
        'text-emerald-700',
        'text-rose-600',
        'text-slate-500'
      );

      if (tone === 'success') {
        copyFeedback.classList.add('text-emerald-700');
      } else if (tone === 'danger') {
        copyFeedback.classList.add('text-rose-600');
      } else {
        copyFeedback.classList.add('text-slate-500');
      }
    };

    const copyViaClipboard = async () => {
      if (!navigator.clipboard || !window.isSecureContext) {
        return false;
      }
      try {
        await navigator.clipboard.writeText(ticket);
        return true;
      } catch (_) {
        return false;
      }
    };

    const copyViaExecCommand = () => {
      const ta = document.createElement('textarea');
      ta.value = ticket;
      ta.setAttribute('readonly', 'readonly');
      ta.style.position      = 'fixed';
      ta.style.opacity        = '0';
      ta.style.pointerEvents  = 'none';
      document.body.appendChild(ta);
      ta.select();
      ta.setSelectionRange(0, ta.value.length);

      let ok = false;
      try {
        ok = document.execCommand('copy');
      } catch (_) {
        ok = false;
      }

      document.body.removeChild(ta);
      return ok;
    };

    copyButton.addEventListener('click', async () => {
      if (!ticket) {
        setFeedback(copyLabels.noTicket, 'danger');
        return;
      }

      const ok = (await copyViaClipboard()) || copyViaExecCommand();

      if (!ok) {
        setFeedback(copyLabels.failMsg, 'danger');
        return;
      }

      copyBtnText.textContent = copyLabels.copied;
      setFeedback(copyLabels.successMsg, 'success');

      window.setTimeout(() => {
        copyBtnText.textContent = copyLabels.copy;
      }, 1800);
    });
  };

  /* ── Expose ── */
  window.getClientSuccessLang = () => activeLang;

  /* ── Init ── */
  applySuccessLanguage(resolveCurrentLang());
  initCopyButton();

  /* ── React to language changes ── */
  window.addEventListener('client-lang-changed', (event) => {
    applySuccessLanguage(event?.detail?.lang || defaultLang);
  });
})();
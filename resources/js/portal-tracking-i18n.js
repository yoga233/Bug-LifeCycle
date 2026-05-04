(() => {
  'use strict';

  const body = document.body;
  if (!body || body.dataset.page !== 'client-tracking') {
    return;
  }

  const trackingI18n = {
    en: {
      tracking_page_title: 'PRANALA BLMS — Ticket Tracking',

      /* ── Header ── */
      tracking_kicker: 'PRANALA BLMS',
      tracking_title_prefix: 'Check',
      tracking_title_emphasis: 'your report status',
      tracking_title_suffix: 'here.',
      tracking_desc: 'Enter your ticket ID to see the latest status and history of your report.',

      /* ── Search ── */
      tracking_label_ticket_number: 'Enter your ticket ID',
      tracking_placeholder_ticket: 'Example: BUG-8F3K2L',
      tracking_btn_track: 'Find Ticket',
      tracking_help: 'You received your ticket ID in the confirmation email after submitting your report. Check your spam folder if you cannot find it.',

      /* ── Error states ── */
      tracking_error_title: 'We could not find that ticket',
      tracking_error_not_found: 'No ticket matches that ID. Double-check your confirmation email or try again.',
      tracking_error_invalid_format: 'That does not look like a valid ticket ID. Ticket IDs look like: BUG-8F3K2L',

      /* ── Summary ── */
      tracking_summary_ticket_number: 'Ticket ID',
      tracking_summary_public_ticket: 'Public Report Ticket',
      tracking_label_reporter: 'Submitted by:',
      tracking_label_report_date: 'Submitted on:',
      tracking_label_severity: 'Severity level:',
      tracking_label_priority: 'Priority:',
      tracking_label_email: 'Email:',
      tracking_priority_unset: 'Being assigned by our team',

      /* ── Timeline ── */
      tracking_timeline_title: 'What has happened so far',
      tracking_timeline_empty: 'No updates yet. Your report has been received and is waiting to be reviewed.',

      /* ── Queue labels ── */
      tracking_queue_label_approved: 'Accepted',
      tracking_queue_label_rejected: 'Needs More Detail',
      tracking_queue_label_expired: 'No Longer Active',
      tracking_queue_label_pending: 'Pending Review',

      /* ── Queue notes ── */
      tracking_queue_note_approved: 'Your report has been reviewed and accepted. It is now being processed by our engineering team.',
      tracking_queue_note_rejected: 'Our team reviewed your report and needs more information to continue. Please submit a new report with additional details.',
      tracking_queue_note_expired: 'Your report is no longer active because it was not processed within the review window. Please submit a new report if the issue still occurs.',
      tracking_queue_note_pending: 'Your report has been received and is waiting to be reviewed. We will notify you by email once it has been reviewed.',

      tracking_processed_at: 'Reviewed on {value}.',
      tracking_pm_notes_label: 'Note from our team:',

      /* ── Empty state ── */
      tracking_empty_state: 'Enter your ticket ID above to see the latest status of your report. Your ticket ID was sent to your email after submitting.',

      /* ── Actions ── */
      tracking_btn_back: 'Back to Home',
      tracking_btn_new_report: 'Submit Another Report',

      /* ── Timeline messages ── */
      tracking_history_created: 'Your report was received and is now in the queue.',
      tracking_history_assignment_canceled: 'Your report is back in the review queue.',
      tracking_history_testing_revision: 'The fix is being revised before final verification.',
      tracking_history_rollback: 'Status was updated to {status}.',

      tracking_history_updated_reported: 'Your report was received and is now in the queue.',
      tracking_history_updated_assigned: 'Your report has been assigned to an engineer.',
      tracking_history_updated_in_progress: 'Our team is actively working on this.',
      tracking_history_updated_testing: 'A fix has been applied and is being verified.',
      tracking_history_updated_resolved: 'The issue has been resolved and verified.',
      tracking_history_updated_closed: 'This ticket has been closed.',
      tracking_history_updated_rejected: 'Your report needs more information before work can continue.',
      tracking_history_updated_default: 'Status was updated to {status}.',
    },

    id: {
      tracking_page_title: 'PRANALA BLMS — Pelacakan Tiket',

      /* ── Header ── */
      tracking_kicker: 'PRANALA BLMS',
      tracking_title_prefix: 'Cek',
      tracking_title_emphasis: 'status laporan',
      tracking_title_suffix: 'Anda.',
      tracking_desc: 'Masukkan ID tiket untuk melihat status terbaru dan riwayat laporan Anda.',

      /* ── Search ── */
      tracking_label_ticket_number: 'Masukkan ID tiket Anda',
      tracking_placeholder_ticket: 'Contoh: BUG-8F3K2L',
      tracking_btn_track: 'Cari Tiket',
      tracking_help: 'ID tiket Anda ada di email konfirmasi yang kami kirim setelah laporan diterima. Cek folder spam jika tidak menemukannya.',

      /* ── Error states ── */
      tracking_error_title: 'Tiket tidak ditemukan',
      tracking_error_not_found: 'Tidak ada tiket yang cocok dengan ID tersebut. Periksa kembali email konfirmasi Anda atau coba lagi.',
      tracking_error_invalid_format: 'Format ID tiket tidak sesuai. ID tiket terlihat seperti ini: BUG-8F3K2L',

      /* ── Summary ── */
      tracking_summary_ticket_number: 'ID Tiket',
      tracking_summary_public_ticket: 'Tiket Laporan Publik',
      tracking_label_reporter: 'Dilaporkan oleh:',
      tracking_label_report_date: 'Tanggal kirim:',
      tracking_label_severity: 'Tingkat keparahan:',
      tracking_label_priority: 'Prioritas:',
      tracking_label_email: 'Email:',
      tracking_priority_unset: 'Sedang ditentukan oleh tim kami',

      /* ── Timeline ── */
      tracking_timeline_title: 'Perkembangan laporan Anda',
      tracking_timeline_empty: 'Belum ada pembaruan. Laporan Anda sudah diterima dan sedang menunggu untuk ditinjau.',

      /* ── Queue labels ── */
      tracking_queue_label_approved: 'Diterima',
      tracking_queue_label_rejected: 'Perlu Detail Tambahan',
      tracking_queue_label_expired: 'Tidak Aktif',
      tracking_queue_label_pending: 'Menunggu Ditinjau',

      /* ── Queue notes ── */
      tracking_queue_note_approved: 'Laporan Anda sudah ditinjau dan diterima. Saat ini sedang diproses oleh tim engineering kami.',
      tracking_queue_note_rejected: 'Tim kami meninjau laporan Anda dan membutuhkan informasi lebih lanjut untuk melanjutkan. Silakan kirim laporan baru dengan detail tambahan.',
      tracking_queue_note_expired: 'Laporan Anda sudah tidak aktif karena tidak diproses dalam jangka waktu peninjauan. Silakan kirim laporan baru jika masalah masih terjadi.',
      tracking_queue_note_pending: 'Laporan Anda sudah diterima dan sedang menunggu ditinjau. Kami akan memberi tahu Anda melalui email setelah ditinjau.',

      tracking_processed_at: 'Ditinjau pada {value}.',
      tracking_pm_notes_label: 'Catatan dari tim kami:',

      /* ── Empty state ── */
      tracking_empty_state: 'Masukkan ID tiket Anda di atas untuk melihat status terbaru laporan Anda. ID tiket dikirim ke email Anda setelah laporan diterima.',

      /* ── Actions ── */
      tracking_btn_back: 'Kembali ke Halaman Utama',
      tracking_btn_new_report: 'Kirim Laporan Baru',

      /* ── Timeline messages ── */
      tracking_history_created: 'Laporan Anda diterima dan sudah masuk antrian.',
      tracking_history_assignment_canceled: 'Laporan Anda kembali ke antrian tinjauan.',
      tracking_history_testing_revision: 'Perbaikan sedang direvisi sebelum verifikasi akhir.',
      tracking_history_rollback: 'Status diperbarui ke {status}.',

      tracking_history_updated_reported: 'Laporan Anda diterima dan sudah masuk antrian.',
      tracking_history_updated_assigned: 'Laporan Anda sudah ditugaskan ke engineer.',
      tracking_history_updated_in_progress: 'Tim kami sedang aktif mengerjakan ini.',
      tracking_history_updated_testing: 'Perbaikan sudah diterapkan dan sedang diverifikasi.',
      tracking_history_updated_resolved: 'Masalah sudah diselesaikan dan diverifikasi.',
      tracking_history_updated_closed: 'Tiket ini sudah ditutup.',
      tracking_history_updated_rejected: 'Laporan Anda membutuhkan informasi tambahan sebelum pengerjaan bisa dilanjutkan.',
      tracking_history_updated_default: 'Status diperbarui ke {status}.',
    },
  };

  const statusLabels = {
    en: {
      Reported: 'Received',
      Assigned: 'Assigned to Team',
      'In Progress': 'In Progress',
      Testing: 'Verifying Fix',
      Resolved: 'Resolved',
      Closed: 'Closed',
      Rejected: 'Needs Information',
    },
    id: {
      Reported: 'Diterima',
      Assigned: 'Sudah Ditugaskan',
      'In Progress': 'Sedang Dikerjakan',
      Testing: 'Verifikasi Perbaikan',
      Resolved: 'Selesai',
      Closed: 'Ditutup',
      Rejected: 'Perlu Informasi Tambahan',
    },
  };

  const defaultLang = 'en';
  const LANG_STORAGE_KEY = 'client-portal-language';
  let activeLang = defaultLang;

  const getPersistedLang = () => {
    try {
      const storedLang = window.localStorage.getItem(LANG_STORAGE_KEY);
      return trackingI18n[storedLang] ? storedLang : null;
    } catch (_) {
      return null;
    }
  };

  const escapeHtml = (value) => String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

  const replaceTemplate = (text, replacements = {}) => String(text || '').replace(/\{(\w+)\}/g, (_, key) => {
    if (Object.prototype.hasOwnProperty.call(replacements, key)) {
      return String(replacements[key]);
    }
    return `{${key}}`;
  });

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

  const getDictionary = () => trackingI18n[activeLang] || trackingI18n[defaultLang] || {};
  const getFallbackDictionary = () => trackingI18n[defaultLang] || {};

  const hasKey = (key) => {
    const currentDictionary = getDictionary();
    const fallbackDictionary = getFallbackDictionary();
    return Object.prototype.hasOwnProperty.call(currentDictionary, key)
      || Object.prototype.hasOwnProperty.call(fallbackDictionary, key);
  };

  const getText = (key, fallback = '') => {
    const currentDictionary = getDictionary();
    const fallbackDictionary = getFallbackDictionary();
    return currentDictionary[key] ?? fallbackDictionary[key] ?? fallback;
  };

  const translateStatus = (statusValue) => {
    const normalizedStatus = String(statusValue || '').trim();
    if (!normalizedStatus) return '';

    const currentLabels = statusLabels[activeLang] || statusLabels[defaultLang] || {};
    const fallbackLabels = statusLabels[defaultLang] || {};

    return currentLabels[normalizedStatus] || fallbackLabels[normalizedStatus] || normalizedStatus;
  };

  const normalizeQueueStatus = (statusValue) => {
    const normalizedStatus = String(statusValue || '').trim().toLowerCase();
    if (['approved', 'rejected', 'expired'].includes(normalizedStatus)) {
      return normalizedStatus;
    }
    return 'pending';
  };

  const normalizeStatusKey = (statusValue) => String(statusValue || '')
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '_')
    .replace(/^_+|_+$/g, '');

  const renderHistoryMessage = (element, template, statusLabel) => {
    const parts = String(template || '').split('{status}');

    if (parts.length < 2) {
      element.textContent = template;
      return;
    }

    const before = parts.shift() ?? '';
    const after = parts.join('{status}');

    element.innerHTML = `${escapeHtml(before)}<span class="font-semibold text-[var(--p)]">${escapeHtml(statusLabel)}</span>${escapeHtml(after)}`;
  };

  const resolveHistoryMessageKey = (historyKind, newStatus) => {
    const normalizedKind = String(historyKind || 'updated').trim();

    if (normalizedKind === 'updated') {
      const statusKey = normalizeStatusKey(newStatus);
      const specificKey = `tracking_history_updated_${statusKey}`;
      if (hasKey(specificKey)) {
        return specificKey;
      }
      return 'tracking_history_updated_default';
    }

    const genericKey = `tracking_history_${normalizedKind}`;
    if (hasKey(genericKey)) {
      return genericKey;
    }

    return 'tracking_history_updated_default';
  };

  const applyTrackingLanguage = (lang) => {
    activeLang = trackingI18n[lang] ? lang : defaultLang;

    document.title = getText('tracking_page_title', document.title);

    document.querySelectorAll('[data-tracking-i18n]').forEach((element) => {
      const key = element.dataset.trackingI18n;
      if (!key) return;

      const translated = getText(key);
      if (translated !== '') {
        element.textContent = translated;
      }
    });

    document.querySelectorAll('[data-tracking-i18n-placeholder]').forEach((element) => {
      const key = element.dataset.trackingI18nPlaceholder;
      if (!key) return;

      const translated = getText(key);
      if (translated !== '') {
        element.setAttribute('placeholder', translated);
      }
    });

    document.querySelectorAll('[data-tracking-error-code]').forEach((element) => {
      const key = element.dataset.trackingErrorCode;
      if (!key) return;

      const translated = getText(key);
      if (translated !== '') {
        element.textContent = translated;
      }
    });

    document.querySelectorAll('[data-tracking-status-value]').forEach((element) => {
      const rawStatus = element.dataset.trackingStatusValue || '';
      const translatedStatus = translateStatus(rawStatus);
      if (!translatedStatus) return;

      const textNode = element.querySelector('span:last-child');
      if (textNode) {
        textNode.textContent = translatedStatus;
        return;
      }

      element.textContent = translatedStatus;
    });

    document.querySelectorAll('[data-tracking-history-message]').forEach((element) => {
      const historyKind = String(element.dataset.historyKind || 'updated').trim();
      const rawNewStatus = element.dataset.newStatus || '';
      const translatedStatus = translateStatus(rawNewStatus);
      const messageKey = resolveHistoryMessageKey(historyKind, rawNewStatus);
      const template = getText(messageKey);

      if (!template) return;

      renderHistoryMessage(element, template, translatedStatus || rawNewStatus);
    });

    document.querySelectorAll('[data-tracking-queue-label]').forEach((element) => {
      const queueStatus = normalizeQueueStatus(element.dataset.queueStatus);
      const labelKey = `tracking_queue_label_${queueStatus}`;
      element.textContent = getText(labelKey, getText('tracking_queue_label_pending', element.textContent));
    });

    document.querySelectorAll('[data-tracking-queue-note]').forEach((element) => {
      const queueStatus = normalizeQueueStatus(element.dataset.queueStatus);
      const noteKey = `tracking_queue_note_${queueStatus}`;
      element.textContent = getText(noteKey, getText('tracking_queue_note_pending', element.textContent));
    });

    document.querySelectorAll('[data-tracking-processed-at]').forEach((element) => {
      const value = element.dataset.value || '';
      const template = getText('tracking_processed_at', element.textContent);
      element.textContent = replaceTemplate(template, { value });
    });

    if (typeof window.__markClientI18nReady === 'function') {
      window.__markClientI18nReady('tracking');
    }
  };

  window.getClientTrackingLang = () => activeLang;

  applyTrackingLanguage(resolveCurrentLang());

  window.addEventListener('client-lang-changed', (event) => {
    applyTrackingLanguage(event?.detail?.lang || defaultLang);
  });
})();
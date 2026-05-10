(() => {
  'use strict';

  const body = document.body;
  if (!body || body.dataset.page !== 'client-report') {
    return;
  }

  const reportI18n = {
    en: {
      /* ── Hero / Page Header ── */
      report_hero_kicker:          'Public Bug Report',
      report_hero_title_prefix:    'Bug reports that are',
      report_hero_title_emphasis:  'structured',
      report_hero_title_suffix:    'so handling stays focused.',
      report_hero_desc:            'Use this form to send a structured report to our engineering team. The more detail you provide, the faster we can identify and fix the issue.',
      report_hero_tag_1:           'Guided report format',
      report_hero_tag_2:           'Screenshot and annotation support',
      report_hero_tag_3:           'Ticket ID sent by email',
      report_hero_tags_aria:       'Bug report page highlights',
      report_hero_btn_start:       'Start Filling the Form',
      report_hero_btn_back:        'Back to Landing Page',

      /* ── Tips sidebar ── */
      report_tips_title:           'Tips for a good report',
      report_tip_1:                'Use a short title that clearly describes what went wrong.',
      report_tip_2:                'Explain what happened and what you expected to happen instead.',
      report_tip_3:                'Write the steps you took in order so we can recreate the issue.',
      report_after_submit_title:   'After you submit',
      report_after_submit_1:       'You will receive a ticket ID at your email address.',
      report_after_submit_2:       'Our team reviews and prioritizes based on severity.',
      report_after_submit_3:       'Track your report\'s progress from the tracking page.',
      report_privacy_title:        'Your data is private',
      report_privacy_desc:         'Your report is only used to investigate the issue and improve system quality.',

      /* ── Form header card ── */
      report_form_kicker:          'Public Bug Report',
      report_form_title:           'Tell us what happened',
      report_form_subtitle:        'Fill in each section clearly. The more detail you provide, the faster we can fix it.',
      report_meta_all_required:    'All fields are required',
      report_required_note:        'All fields are required',
      report_error_title:          'Please review your form before submitting.',
      report_error_subtitle:       'The following fields need attention.',

      /* ── Section: Reporter ── */
      report_section_reporter:     'Your Information',
      report_label_guest_name:     'Full Name',
      report_placeholder_guest_name: 'John Doe',
      report_field_label_guest_name: 'Full name',

      report_label_guest_email:    'Email Address',
      report_placeholder_guest_email: 'you@company.com',
      report_field_label_guest_email: 'Email address',
      report_guest_email_hint:     'Your ticket ID and status updates will be sent here.',

      report_label_guest_company:  'Company / Organization',
      report_placeholder_guest_company: 'PT Example Indonesia',
      report_field_label_guest_company: 'Company / Organization',

      report_label_guest_position: 'Job Title',
      report_placeholder_guest_position: 'QA Engineer',
      report_field_label_guest_position: 'Job title',

      /* ── Section: Issue Context ── */
      report_section_bug_details:  'Issue Context',

      report_label_guest_version:  'App Version',
      report_placeholder_guest_version: 'v2.14.3 or Chrome Browser',
      report_field_label_guest_version: 'App version',

      report_label_project:        'Proyek',
      report_field_label_project:  'Proyek',

      report_label_severity:       'Keparahan',
      report_field_label_severity: 'Keparahan',

      report_label_frequency:      'Frequency',
      report_field_label_frequency:'Frequency',

      /* ── Section: Issue Title ── */
      report_section_issue_title:  'Issue Title',
      report_label_title:          'Title',
      report_placeholder_title:    'Save button not working on Profile page',
      report_field_label_title:    'Issue title',

      /* ── Section: Description ── */
      report_section_description:  'Description',
      report_label_description:    'What happened?',
      report_placeholder_description: 'Describe what happened and what you expected instead.',
      report_field_label_description: 'Description',
      report_description_hint:     '',

      /* ── Section: Steps ── */
      report_section_steps:        'Steps to Recreate',
      report_label_reproduction_steps: 'What did you do?',
      report_placeholder_reproduction_steps:
        '1. Opened the app and logged in\n2. Went to [page]\n3. Clicked [button]\n4. Issue appeared',
      report_field_label_reproduction_steps: 'Steps to recreate',
      report_repro_hint:           '',

      /* ── Section: Screenshots ── */
      report_section_attachments:  'Screenshots',
      report_label_attachments:    'Attach images',
      report_field_label_attachments: 'Screenshots',
      report_attachments_hint:     '',
      report_upload_empty_text:    'Drop images here or click to browse',
      report_upload_empty_subtext: 'JPG, PNG, WEBP, GIF — max 5 files',
      report_preview_title:        'Attached',
      report_preview_add_image:    'Add',
      report_preview_hint_text:    'Click Annotate to mark problem areas.',
      report_preview_badge_annotated: 'Annotated',
      report_preview_remove_label: 'Remove',

      report_select_placeholder_project:   'Select project',
      report_select_placeholder_severity:  'Select severity',
      report_select_placeholder_frequency: 'Select frequency',

      /* ── Annotation ── */
      report_annotation_editor_title:       'Mark the Problem Area',
      report_annotation_close:              'Close',
      report_annotation_toolbar_aria_label: 'Annotation toolbar',
      report_annotation_tool_select:        'Select',
      report_annotation_tool_rectangle:     'Rectangle',
      report_annotation_tool_arrow:         'Arrow',
      report_annotation_tool_freehand:      'Freehand',
      report_annotation_tool_text:          'Text',
      report_annotation_tool_redact:        'Blur / Hide',
      report_annotation_tool_marker:        'Number Pin',
      report_annotation_color_label:        'Color',
      report_annotation_color_red:          'Red',
      report_annotation_color_yellow:       'Yellow',
      report_annotation_color_green:        'Green',
      report_annotation_color_blue:         'Blue',
      report_annotation_color_black:        'Black',
      report_annotation_stroke_label:       'Stroke',
      report_annotation_stroke_thin:        'Thin',
      report_annotation_stroke_medium:      'Medium',
      report_annotation_stroke_thick:       'Thick',
      report_annotation_undo:               'Undo',
      report_annotation_redo:               'Redo',
      report_annotation_delete_selected:    'Delete',
      report_annotation_clear_all:          'Clear All',
      report_annotation_save:               'Save',
      report_marker_notes_title:            'Pin Notes',
      report_marker_notes_placeholder:      'Pin {index} description',
      report_annotation_text_placeholder:   'Add a note',
      report_annotation_result_label:       'Annotated screenshot preview',
      report_annotation_result_alt:         'Annotated output — {name}',

      /* ── Annotation status messages ── */
      report_annotation_status_default:             'Upload a screenshot to start marking.',
      report_annotation_status_ready:               'Screenshot loaded. Choose a tool to start.',
      report_annotation_status_tool_select_active:  'Select mode.',
      report_annotation_status_tool_rectangle_active: 'Rectangle mode.',
      report_annotation_status_tool_arrow_active:   'Arrow mode.',
      report_annotation_status_tool_freehand_active:'Freehand mode.',
      report_annotation_status_tool_text_active:    'Text mode.',
      report_annotation_status_tool_redact_active:  'Blur mode.',
      report_annotation_status_tool_marker_active:  'Number pin mode.',
      report_annotation_status_marker_added:        'Pin {index} added.',
      report_annotation_status_delete_empty:        'Select a marking first.',
      report_annotation_status_clear_success:       'All markings cleared.',
      report_annotation_status_undo_empty:          'Nothing to undo.',
      report_annotation_status_redo_empty:          'Nothing to redo.',
      report_annotation_status_undo_success:        'Undone.',
      report_annotation_status_redo_success:        'Restored.',
      report_annotation_status_select_image_save:   'Select a screenshot first.',
      report_annotation_status_editor_unavailable:  'Annotation editor unavailable.',
      report_annotation_status_save_unsupported:    'Browser does not support auto-save.',
      report_annotation_status_save_failed:         'Could not save. Please try again.',
      report_annotation_status_saved:               'Saved. Annotated screenshot will be included.',
      report_annotation_status_history_restored:    'Annotation restored.',
      report_annotation_status_browser_save_unsupported:   'Browser does not support auto-save.',
      report_annotation_status_browser_remove_unsupported: 'Browser does not support auto-remove. Please reselect files.',
      report_annotation_status_load_failed:         'Could not load screenshot into editor.',
      report_annotation_status_image_unprocessable: 'This image cannot be processed.',

      /* ── Submit card ── */
      report_meta_note:            'After submitting, your ticket ID will be sent to your email.',
      report_submit_button:        'Send Report',

      /* ── Preview buttons ── */
      report_preview_button_annotating: 'Annotating',
      report_preview_button_annotate:   'Annotate',
      report_preview_label_non_image:   'Non-image file',
      report_preview_remove:            '✕',
      report_preview_remove_aria:       'Remove {name}',

      /* ── Validation messages ── */
      report_validation_required:             'Please fill in {field}.',
      report_validation_invalid_email:        'Please enter a valid email address.',
      report_validation_exists:               'Please select a valid {field}.',
      report_validation_invalid_option:       'Please choose a valid option for {field}.',
      report_validation_max_characters:       '{field} must be {max} characters or fewer.',
      report_validation_image_only:           'Only image files are allowed (JPG, PNG, WEBP, GIF).',
      report_validation_max_attachments:      'You can attach up to {count} images.',
      report_validation_attachment_required:  'Please attach at least 1 screenshot.',
      report_validation_attachment_file_max:  'Each image must be {size} MB or smaller.',
      report_validation_file:                 '{field} must be a valid file.',
      report_validation_mimes:                '{field} must be: {values}.',
      report_warning_incomplete_title:        'Please complete all fields before submitting:',

      /* ── Footer ── */
      foot_privacy: 'Privacy Policy',
      foot_terms:   'Terms of Service',
    },

    id: {
      /* ── Hero / Page Header ── */
      report_hero_kicker:          'Laporan Bug Publik',
      report_hero_title_prefix:    'Laporan bug yang',
      report_hero_title_emphasis:  'terstruktur',
      report_hero_title_suffix:    'membuat penanganan lebih terarah.',
      report_hero_desc:            'Gunakan form ini untuk mengirim laporan ke tim engineering kami. Semakin detail yang Anda berikan, semakin cepat kami bisa menemukan dan memperbaiki masalahnya.',
      report_hero_tag_1:           'Format laporan terarah',
      report_hero_tag_2:           'Dukungan screenshot dan anotasi',
      report_hero_tag_3:           'ID tiket dikirim ke email',
      report_hero_tags_aria:       'Keunggulan halaman laporan bug',
      report_hero_btn_start:       'Mulai Isi Form',
      report_hero_btn_back:        'Kembali ke Halaman Utama',

      /* ── Tips sidebar ── */
      report_tips_title:           'Tips laporan yang baik',
      report_tip_1:                'Gunakan judul singkat yang langsung menjelaskan apa yang salah.',
      report_tip_2:                'Jelaskan apa yang terjadi dan apa yang seharusnya muncul.',
      report_tip_3:                'Tulis langkah yang Anda lakukan secara berurutan agar kami bisa melihat masalah yang sama.',
      report_after_submit_title:   'Setelah Anda mengirim',
      report_after_submit_1:       'Anda akan menerima ID tiket di alamat email Anda.',
      report_after_submit_2:       'Tim kami meninjau dan memprioritaskan berdasarkan tingkat keparahan.',
      report_after_submit_3:       'Pantau perkembangan laporan dari halaman pelacakan.',
      report_privacy_title:        'Data Anda bersifat rahasia',
      report_privacy_desc:         'Laporan Anda hanya digunakan untuk menginvestigasi masalah dan meningkatkan kualitas sistem.',

      /* ── Form header card ── */
      report_form_kicker:          'Laporan Bug Publik',
      report_form_title:           'Ceritakan masalah yang Anda alami',
      report_form_subtitle:        'Isi setiap bagian dengan jelas. Semakin detail, semakin cepat kami bisa memperbaikinya.',
      report_meta_all_required:    'Semua field wajib diisi',
      report_required_note:        'Semua field wajib diisi',
      report_error_title:          'Mohon periksa form Anda sebelum mengirim.',
      report_error_subtitle:       'Field berikut perlu dilengkapi.',

      report_select_placeholder_project:   'Pilih proyek',
      report_select_placeholder_severity:  'Pilih keparahan',
      report_select_placeholder_frequency: 'Pilih frekuensi',

      /* ── Section: Reporter ── */
      report_section_reporter:     'Informasi Anda',
      report_label_guest_name:     'Nama Lengkap',
      report_placeholder_guest_name: 'John Doe',
      report_field_label_guest_name: 'Nama lengkap',

      report_label_guest_email:    'Alamat Email',
      report_placeholder_guest_email: 'anda@perusahaan.com',
      report_field_label_guest_email: 'Alamat email',
      report_guest_email_hint:     'ID tiket dan pembaruan status akan dikirim ke sini.',

      report_label_guest_company:  'Perusahaan / Organisasi',
      report_placeholder_guest_company: 'PT Contoh Indonesia',
      report_field_label_guest_company: 'Perusahaan / Organisasi',

      report_label_guest_position: 'Jabatan',
      report_placeholder_guest_position: 'QA Engineer',
      report_field_label_guest_position: 'Jabatan',

      /* ── Section: Issue Context ── */
      report_section_bug_details:  'Konteks Masalah',

      report_label_guest_version:  'Versi Aplikasi',
      report_placeholder_guest_version: 'v2.14.3 atau Chrome Browser',
      report_field_label_guest_version: 'Versi aplikasi',

      report_label_project:        'Project',
      report_field_label_project:  'Project',

      report_label_severity:       'Severity',
      report_field_label_severity: 'Severity',

      report_label_frequency:      'Frekuensi',
      report_field_label_frequency:'Frekuensi',

      /* ── Section: Issue Title ── */
      report_section_issue_title:  'Judul Masalah',
      report_label_title:          'Judul',
      report_placeholder_title:    'Tombol simpan tidak berfungsi di halaman Profil',
      report_field_label_title:    'Judul masalah',

      /* ── Section: Description ── */
      report_section_description:  'Deskripsi',
      report_label_description:    'Apa yang terjadi?',
      report_placeholder_description: 'Jelaskan apa yang terjadi dan apa yang seharusnya muncul.',
      report_field_label_description: 'Deskripsi',
      report_description_hint:     '',

      /* ── Section: Steps ── */
      report_section_steps:        'Langkah Reproduksi',
      report_label_reproduction_steps: 'Apa yang Anda lakukan?',
      report_placeholder_reproduction_steps:
        '1. Buka aplikasi dan login\n2. Pergi ke [halaman]\n3. Klik [tombol]\n4. Masalah muncul',
      report_field_label_reproduction_steps: 'Langkah reproduksi',
      report_repro_hint:           '',

      /* ── Section: Screenshots ── */
      report_section_attachments:  'Screenshot',
      report_label_attachments:    'Lampirkan gambar',
      report_field_label_attachments: 'Screenshot',
      report_attachments_hint:     '',
      report_upload_empty_text:    'Seret gambar ke sini atau klik untuk memilih',
      report_upload_empty_subtext: 'JPG, PNG, WEBP, GIF — maks 5 file',
      report_preview_title:        'Terlampir',
      report_preview_add_image:    'Tambah',
      report_preview_hint_text:    'Klik Annotate untuk menandai area bermasalah.',
      report_preview_badge_annotated: 'Sudah Ditandai',
      report_preview_remove_label: 'Hapus',

      /* ── Annotation ── */
      report_annotation_editor_title:       'Tandai Area yang Bermasalah',
      report_annotation_close:              'Tutup',
      report_annotation_toolbar_aria_label: 'Toolbar anotasi',
      report_annotation_tool_select:        'Pilih',
      report_annotation_tool_rectangle:     'Persegi',
      report_annotation_tool_arrow:         'Panah',
      report_annotation_tool_freehand:      'Gambar Bebas',
      report_annotation_tool_text:          'Teks',
      report_annotation_tool_redact:        'Blur / Sembunyikan',
      report_annotation_tool_marker:        'Pin Bernomor',
      report_annotation_color_label:        'Warna',
      report_annotation_color_red:          'Merah',
      report_annotation_color_yellow:       'Kuning',
      report_annotation_color_green:        'Hijau',
      report_annotation_color_blue:         'Biru',
      report_annotation_color_black:        'Hitam',
      report_annotation_stroke_label:       'Ketebalan',
      report_annotation_stroke_thin:        'Tipis',
      report_annotation_stroke_medium:      'Sedang',
      report_annotation_stroke_thick:       'Tebal',
      report_annotation_undo:               'Undo',
      report_annotation_redo:               'Redo',
      report_annotation_delete_selected:    'Hapus',
      report_annotation_clear_all:          'Hapus Semua',
      report_annotation_save:               'Simpan',
      report_marker_notes_title:            'Catatan Pin',
      report_marker_notes_placeholder:      'Deskripsi pin {index}',
      report_annotation_text_placeholder:   'Tambahkan catatan',
      report_annotation_result_label:       'Preview screenshot yang sudah ditandai',
      report_annotation_result_alt:         'Hasil tanda — {name}',

      /* ── Annotation status messages ── */
      report_annotation_status_default:             'Upload screenshot untuk mulai menandai.',
      report_annotation_status_ready:               'Screenshot siap. Pilih tool untuk mulai.',
      report_annotation_status_tool_select_active:  'Mode pilih.',
      report_annotation_status_tool_rectangle_active: 'Mode persegi.',
      report_annotation_status_tool_arrow_active:   'Mode panah.',
      report_annotation_status_tool_freehand_active:'Mode gambar bebas.',
      report_annotation_status_tool_text_active:    'Mode teks.',
      report_annotation_status_tool_redact_active:  'Mode blur.',
      report_annotation_status_tool_marker_active:  'Mode pin bernomor.',
      report_annotation_status_marker_added:        'Pin {index} ditambahkan.',
      report_annotation_status_delete_empty:        'Pilih tanda terlebih dahulu.',
      report_annotation_status_clear_success:       'Semua tanda dihapus.',
      report_annotation_status_undo_empty:          'Tidak ada yang bisa di-undo.',
      report_annotation_status_redo_empty:          'Tidak ada yang bisa di-redo.',
      report_annotation_status_undo_success:        'Dibatalkan.',
      report_annotation_status_redo_success:        'Dikembalikan.',
      report_annotation_status_select_image_save:   'Pilih screenshot terlebih dahulu.',
      report_annotation_status_editor_unavailable:  'Editor anotasi tidak tersedia.',
      report_annotation_status_save_unsupported:    'Browser tidak mendukung penyimpanan otomatis.',
      report_annotation_status_save_failed:         'Gagal menyimpan. Silakan coba lagi.',
      report_annotation_status_saved:               'Tersimpan. Screenshot yang ditandai akan disertakan.',
      report_annotation_status_history_restored:    'Anotasi dipulihkan.',
      report_annotation_status_browser_save_unsupported:   'Browser tidak mendukung penyimpanan otomatis.',
      report_annotation_status_browser_remove_unsupported: 'Browser tidak mendukung penghapusan otomatis. Pilih ulang file.',
      report_annotation_status_load_failed:         'Gagal memuat screenshot ke editor.',
      report_annotation_status_image_unprocessable: 'Gambar ini tidak bisa diproses.',

      /* ── Submit card ── */
      report_meta_note:            'Setelah mengirim, ID tiket akan dikirim ke email Anda.',
      report_submit_button:        'Kirim Laporan',

      /* ── Preview buttons ── */
      report_preview_button_annotating: 'Sedang Ditandai',
      report_preview_button_annotate:   'Annotate',
      report_preview_label_non_image:   'Bukan file gambar',
      report_preview_remove:            '✕',
      report_preview_remove_aria:       'Hapus {name}',

      /* ── Validation messages ── */
      report_validation_required:             'Mohon isi {field}.',
      report_validation_invalid_email:        'Mohon masukkan alamat email yang valid.',
      report_validation_exists:               'Mohon pilih {field} yang valid.',
      report_validation_invalid_option:       'Mohon pilih opsi yang valid untuk {field}.',
      report_validation_max_characters:       '{field} maksimal {max} karakter.',
      report_validation_image_only:           'Hanya file gambar yang diperbolehkan (JPG, PNG, WEBP, GIF).',
      report_validation_max_attachments:      'Anda bisa melampirkan hingga {count} gambar.',
      report_validation_attachment_required:  'Mohon lampirkan minimal 1 screenshot.',
      report_validation_attachment_file_max:  'Setiap gambar maksimal {size} MB.',
      report_validation_file:                 '{field} harus berupa file yang valid.',
      report_validation_mimes:                'Format {field} harus: {values}.',
      report_warning_incomplete_title:        'Mohon lengkapi semua field sebelum mengirim:',

      /* ── Footer ── */
      foot_privacy: 'Kebijakan Privasi',
      foot_terms:   'Ketentuan Layanan',
    },
  };

  const defaultLang = 'en';
  const LANG_STORAGE_KEY = 'client-portal-language';
  let activeLang = defaultLang;

  const getPersistedLang = () => {
    try {
      const storedLang = window.localStorage.getItem(LANG_STORAGE_KEY);
      return reportI18n[storedLang] ? storedLang : null;
    } catch (_) {
      return null;
    }
  };

  const getReportErrorMeta = () => {
    const form = document.getElementById('clientReportForm');
    const raw = form?.dataset?.reportErrorMeta;
    if (!raw) return {};
    try {
      const parsed = JSON.parse(raw);
      return parsed && typeof parsed === 'object' ? parsed : {};
    } catch (_) {
      return {};
    }
  };

  const getFieldLabel = (field, fieldLabelKey) => {
    if (fieldLabelKey) {
      const translatedFromKey = getText(fieldLabelKey, {}, '');
      if (translatedFromKey) return translatedFromKey;
    }

    const normalizedField = String(field || '').trim();
    if (!normalizedField) return '';

    const safeField = normalizedField
      .replace(/\./g, '\\.')
      .replace(/\[/g, '\\[')
      .replace(/\]/g, '\\]');

    const input =
      document.querySelector(`[name="${safeField}"]`) ||
      document.querySelector(`[name="${safeField}[]"]`);

    if (
      input &&
      typeof input.dataset?.fieldLabel === 'string' &&
      input.dataset.fieldLabel.trim() !== ''
    ) {
      return input.dataset.fieldLabel.trim();
    }

    return normalizedField;
  };

  const applyServerValidationErrorTranslations = () => {
    const errorMeta = getReportErrorMeta();

    document.querySelectorAll('.report-error-text[data-report-error-field]').forEach((element) => {
      if (element.classList.contains('hidden')) return;

      const field = String(element.dataset.reportErrorField || '').trim();
      if (!field) return;

      const meta = errorMeta[field];
      if (!meta || typeof meta !== 'object') return;

      const messageKey = typeof meta.message_key === 'string' ? meta.message_key : '';
      if (!messageKey) return;

      const replacements = meta.replacements && typeof meta.replacements === 'object'
        ? { ...meta.replacements }
        : {};

      replacements.field = getFieldLabel(
        field,
        typeof meta.field_label_key === 'string' ? meta.field_label_key : ''
      );

      const translated = getText(messageKey, replacements, '');
      if (!translated) return;

      element.textContent = translated;
    });
  };

  const replaceTemplate = (text, replacements = {}) =>
    String(text || '').replace(/\{(\w+)\}/g, (_, key) => {
      if (Object.prototype.hasOwnProperty.call(replacements, key)) {
        return String(replacements[key]);
      }
      return `{${key}}`;
    });

  const getText = (key, replacements = {}, fallback = '') => {
    const currentDictionary = reportI18n[activeLang] || reportI18n[defaultLang] || {};
    const fallbackDictionary = reportI18n[defaultLang] || {};
    const raw =
      currentDictionary[key] ??
      fallbackDictionary[key] ??
      fallback ??
      '';
    return replaceTemplate(raw, replacements);
  };

    const getCustomSelectHiddenInput = (wrapper) => {
    const fieldName = String(wrapper?.dataset?.fieldName || '').trim();
    if (!fieldName) return null;

    return (
      wrapper.querySelector(`input[name="${fieldName}"]`) ||
      wrapper.querySelector(`input[name="${fieldName}[]"]`)
    );
  };

  const getCustomSelectTriggerButton = (wrapper) =>
    wrapper.querySelector('[x-data] > button');

  const setCustomSelectTriggerText = (button, text) => {
    if (!button) return;

    const labelElement =
      button.querySelector('[data-report-select-label]') ||
      button.querySelector('[data-dropdown-label]') ||
      button.querySelector('.truncate') ||
      button.querySelector('span');

    if (labelElement) {
      labelElement.textContent = text;
      return;
    }

    const textNode = Array.from(button.childNodes).find(
      (node) => node.nodeType === Node.TEXT_NODE
    );

    if (textNode) {
      textNode.textContent = ` ${text} `;
      return;
    }

    button.textContent = text;
  };

  const applyCustomSelectPlaceholders = () => {
    document.querySelectorAll('[data-report-select-placeholder-key]').forEach((wrapper) => {
      const placeholderKey = wrapper.dataset.reportSelectPlaceholderKey;
      if (!placeholderKey) return;

      const hiddenInput = getCustomSelectHiddenInput(wrapper);
      const hasValue = Boolean(hiddenInput?.value?.trim());

      if (hasValue) return;

      const triggerButton = getCustomSelectTriggerButton(wrapper);
      if (!triggerButton) return;

      const placeholderText = getText(placeholderKey, {}, '');
      if (!placeholderText) return;

      setCustomSelectTriggerText(triggerButton, placeholderText);
    });
  };

    // ─────────────────────────────────────────────
  // Re-validate visible errors on language change
  // ─────────────────────────────────────────────

  const revalidateVisibleClientErrors = () => {
    const form = document.getElementById('clientReportForm');
    if (!form) return;

    // ── 1. Re-translate input & textarea errors ──
    form.querySelectorAll('[data-required="true"]').forEach((input) => {
      if (input.type === 'file') return;

      const errorId = input.dataset.errorId;
      const errorEl = document.getElementById(errorId);
      if (!errorEl || errorEl.classList.contains('hidden') || !errorEl.textContent.trim()) return;

      const value = input.value.trim();
      const fieldLabel = (() => {
        const i18nKey = input.dataset.reportI18nFieldLabel;
        if (i18nKey) {
          const translated = getText(i18nKey, {}, '');
          if (translated) return translated;
        }
        return input.dataset.fieldLabel || input.name || 'This field';
      })();

      if (!value) {
        errorEl.textContent = getText(
          'report_validation_required',
          { field: fieldLabel },
          `Please fill in ${fieldLabel}.`
        );
      } else if (input.type === 'email') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
        if (!emailRegex.test(value)) {
          errorEl.textContent = getText(
            'report_validation_invalid_email',
            {},
            'Please enter a valid email address.'
          );
        }
      }
    });

    // ── 2. Re-translate custom select errors ──
    form.querySelectorAll('[data-required-select="true"]').forEach((wrapper) => {
      const errorId = wrapper.dataset.errorId;
      const errorEl = document.getElementById(errorId);
      if (!errorEl || errorEl.classList.contains('hidden') || !errorEl.textContent.trim()) return;

      const fieldName = wrapper.dataset.fieldName;
      const hiddenInput =
        wrapper.querySelector(`input[name="${fieldName}"]`) ||
        wrapper.querySelector(`input[name="${fieldName}[]"]`);
      const value = hiddenInput ? hiddenInput.value.trim() : '';

      if (!value) {
        const fieldLabel = (() => {
          const i18nKey = wrapper.dataset.reportI18nFieldLabel;
          if (i18nKey) {
            const translated = getText(i18nKey, {}, '');
            if (translated) return translated;
          }
          return wrapper.dataset.fieldLabel || fieldName || 'option';
        })();

        errorEl.textContent = getText(
          'report_validation_required',
          { field: fieldLabel },
          `Please fill in ${fieldLabel}.`
        );
      }
    });

    // ── 3. Re-translate attachment error ──
    const attachmentError = document.getElementById('error-attachments');
    if (
      attachmentError &&
      !attachmentError.classList.contains('hidden') &&
      attachmentError.dataset.clientErrorType === 'attachment-required'
    ) {
      const currentLang = activeLang === 'id' ? 'id' : 'en';
      const fallbackMessages = {
        en: 'Attach at least 1 image before submitting report.',
        id: 'Lampirkan minimal 1 gambar sebelum mengirim laporan.',
      };

      attachmentError.textContent = getText(
        'report_validation_attachment_required',
        {},
        fallbackMessages[currentLang] || fallbackMessages.en
      );
    }
  };

  const applyReportLanguage = (lang) => {
    activeLang = reportI18n[lang] ? lang : defaultLang;
    const dictionary = reportI18n[activeLang];

    /* textContent */
    document.querySelectorAll('[data-report-i18n]').forEach((element) => {
      const key = element.dataset.reportI18n;
      if (key && dictionary[key] !== undefined) {
        element.textContent = dictionary[key];
      }
    });

    /* aria-label */
    document.querySelectorAll('[data-report-i18n-aria-label]').forEach((element) => {
      const key = element.dataset.reportI18nAriaLabel;
      if (key && dictionary[key] !== undefined) {
        element.setAttribute('aria-label', dictionary[key]);
      }
    });

    /* title attribute */
    document.querySelectorAll('[data-report-i18n-title]').forEach((element) => {
      const key = element.dataset.reportI18nTitle;
      if (key && dictionary[key] !== undefined) {
        element.setAttribute('title', dictionary[key]);
      }
    });

    /* placeholder */
    document.querySelectorAll('[data-report-i18n-placeholder]').forEach((element) => {
      const key = element.dataset.reportI18nPlaceholder;
      if (key && dictionary[key] !== undefined) {
        element.setAttribute('placeholder', dictionary[key]);
      }
    });

    /* data-field-label */
    document.querySelectorAll('[data-report-i18n-field-label]').forEach((element) => {
      const key = element.dataset.reportI18nFieldLabel;
      if (key && dictionary[key] !== undefined) {
        element.dataset.fieldLabel = dictionary[key];
      }
    });

    applyServerValidationErrorTranslations();

    requestAnimationFrame(() => {
      applyCustomSelectPlaceholders();
    });

    // Re-validate visible client-side errors so they switch language
    revalidateVisibleClientErrors();

    if (typeof window.__markClientI18nReady === 'function') {
      window.__markClientI18nReady('report');
    }
  };

  const resolveCurrentLang = () => {
    const persistedLang = getPersistedLang();
    if (persistedLang) return persistedLang;
    if (typeof window.__clientInitialLang === 'string') return window.__clientInitialLang;
    if (typeof window.getClientLandingLang === 'function') return window.getClientLandingLang();
    if (typeof window.__clientLandingLang === 'string') return window.__clientLandingLang;
    return 'en';
  };

  window.getClientReportLang = () => activeLang;
  window.getClientReportText = (key, replacements = {}, fallback = '') =>
    getText(key, replacements, fallback);

  applyReportLanguage(resolveCurrentLang());

  window.addEventListener('client-lang-changed', (event) => {
    applyReportLanguage(event?.detail?.lang || defaultLang);
  });
})();
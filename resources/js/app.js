import './bootstrap';

import Alpine from 'alpinejs';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';

window.Alpine = Alpine;

// Project Manager: comment section (AJAX submit + append without reload)
window.pmCommentSection = function pmCommentSection({ postUrl, csrf, initialComments }) {
    return {
        postUrl,
        csrf,
        comments: Array.isArray(initialComments) ? initialComments : [],
        groupedComments: [],
        content: '',
        submitting: false,
        error: '',

        // UX: group consecutive comments from the same user in the same timestamp bucket
        // (similar to Slack/Teams). This reduces visual density without touching backend.
        // Grouping rule:
        // - same user_name
        // - same created_at (minute precision as formatted by backend)
        groupComments() {
            const src = Array.isArray(this.comments) ? this.comments : [];
            const groups = [];

            for (const c of src) {
                const last = groups[groups.length - 1];
                const sameUser = last?.user_name && c?.user_name && last.user_name === c.user_name;
                const sameTime = last?.created_at && c?.created_at && last.created_at === c.created_at;

                if (last && sameUser && sameTime) {
                    last.items.push({
                        id: c.id,
                        content: c.content,
                    });
                    continue;
                }

                groups.push({
                    user_name: c.user_name,
                    user_initial: c.user_initial,
                    created_at: c.created_at,
                    items: [
                        {
                            id: c.id,
                            content: c.content,
                        },
                    ],
                });
            }

            this.groupedComments = groups;
        },

        init() {
            this.groupComments();
        },

        async submit() {
            this.error = '';

            const content = (this.content || '').trim();
            if (content.length < 2) {
                this.error = 'Komentar minimal 2 karakter.';
                return;
            }

            if (this.submitting) return;
            this.submitting = true;

            try {
                const res = await fetch(this.postUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(this.csrf ? { 'X-CSRF-TOKEN': this.csrf } : {}),
                    },
                    body: JSON.stringify({ content }),
                });

                const data = await res.json().catch(() => null);
                if (!res.ok) {
                    // Laravel validation error format: { message, errors: { content: [..] } }
                    const msg =
                        data?.errors?.content?.[0] ||
                        data?.message ||
                        'Gagal mengirim komentar.';
                    this.error = msg;
                    return;
                }

                if (data?.comment) {
                    this.comments.push(data.comment);
                    this.groupComments();
                }

                this.content = '';
                // Keep the user calm: don't auto-scroll; stay at current position.
            } catch (e) {
                this.error = 'Gagal mengirim komentar. Coba lagi.';
            } finally {
                this.submitting = false;
            }
        },
    };
};

// Project Manager: assignment section (AJAX assign/reassign/unassign without reload)
window.pmAssignmentSection = function pmAssignmentSection({
    assignUrl,
    unassignUrl,
    updatePriorityUrl,
    csrf,
    initialBug,
    initialAssigneeName,
    canAssign,
    canEditPriority,
    initialPriority,
}) {
    return {
        assignUrl,
        unassignUrl,
        updatePriorityUrl,
        csrf,
        canAssign: !!canAssign,
        canEditPriority: !!canEditPriority,
        bug: initialBug || { id: null, status: null },
        assigneeName: initialAssigneeName || '—',
        priority: initialPriority || null,

        // form state
        assigneeId: '',
        assigneeNameSelected: '',
        priorityId: '',
        submitting: false,
        prioritySubmitting: false,
        error: '',

        init() {
            // set preselected state
            this.assigneeId = (this.bug?.assignee_id ?? '')?.toString?.() || '';
            this.priorityId = (this.priority?.id ?? '')?.toString?.() || '';
            this.syncUiPermissions();
        },

        syncUiPermissions() {
            this.canAssign = ['Reported', 'Assigned'].includes(this.bug?.status);
            this.canEditPriority = this.bug?.status === 'Reported';
        },

        priorityBadgeStyle(priority) {
            if (!priority) {
                return '';
            }

            const level = String(priority.level || '').toUpperCase();
            const fallback = (() => {
                switch (level) {
                    case 'URGENT':
                        return { bg: '#FEE2E2', text: '#DC2626' };
                    case 'HIGH':
                        return { bg: '#FEF3C7', text: '#D97706' };
                    case 'MEDIUM':
                        return { bg: '#DBEAFE', text: '#2563EB' };
                    case 'LOW':
                        return { bg: '#F3F4F6', text: '#6B7280' };
                    default:
                        return { bg: '#F1F5F9', text: '#475569' };
                }
            })();

            const bg = priority.bg_color || fallback.bg;
            const text = priority.text_color || fallback.text;
            return `background-color: ${bg}; color: ${text};`;
        },

        selectedName() {
            return (
                this.$root?.querySelector('select[name=assignee_id]')?.selectedOptions?.[0]?.text ||
                ''
            ).trim();
        },

        dispatchToast(type, message) {
            // Global toast (fixed overlay). Implemented by <x-flash-toast />.
            window.dispatchEvent(
                new CustomEvent('app-toast', {
                    detail: {
                        type: type || 'success',
                        message: message || '',
                    },
                })
            );
        },

        async postJson(url, payload) {
            const res = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(this.csrf ? { 'X-CSRF-TOKEN': this.csrf } : {}),
                },
                body: JSON.stringify(payload || {}),
            });

            const data = await res.json().catch(() => null);
            return { res, data };
        },

        applyBugPayload(data) {
            const bug = data?.bug;
            if (!bug) return;

            // update state
            this.bug.status = bug.status;

            if (bug.assignee?.name) {
                this.assigneeName = bug.assignee.name;
            } else {
                this.assigneeName = '—';
            }

            if (Object.prototype.hasOwnProperty.call(bug, 'priority')) {
                if (bug.priority?.id) {
                    const mergedPriority = {
                        ...(this.priority || {}),
                        ...bug.priority,
                    };

                    this.priority = mergedPriority;
                    this.priorityId = String(mergedPriority.id);
                } else {
                    this.priority = null;
                    this.priorityId = '';
                }
            }

            this.syncUiPermissions();
        },

        async submitPriority() {
            this.error = '';

            if (!this.canEditPriority) {
                const msg = 'Prioritas hanya bisa diubah saat status masih Dilaporkan.';
                this.error = msg;
                this.dispatchToast('error', msg);
                return;
            }

            if (!this.priorityId) {
                const msg = 'Pilih prioritas terlebih dahulu.';
                this.error = msg;
                this.dispatchToast('error', msg);
                return;
            }

            if (!this.updatePriorityUrl || this.prioritySubmitting) {
                return;
            }

            this.prioritySubmitting = true;

            try {
                const { res, data } = await this.postJson(this.updatePriorityUrl, {
                    priority_id: parseInt(this.priorityId, 10),
                });

                if (!res.ok) {
                    const msg =
                        data?.errors?.priority_id?.[0] ||
                        data?.message ||
                        'Gagal memperbarui prioritas.';
                    this.error = msg;
                    this.dispatchToast('error', msg);
                    return;
                }

                this.applyBugPayload(data);
                this.dispatchToast('success', data?.message || 'Prioritas berhasil diperbarui.');
            } catch (e) {
                const msg = 'Gagal memperbarui prioritas. Coba lagi.';
                this.error = msg;
                this.dispatchToast('error', msg);
            } finally {
                this.prioritySubmitting = false;
            }
        },

        statusLabel(status) {
            switch (status) {
                case 'Reported':
                    return 'Dilaporkan';
                case 'Assigned':
                    return 'Ditugaskan';
                case 'In Progress':
                    return 'Dalam Pengerjaan';
                case 'Testing':
                    return 'Pengujian';
                case 'Resolved':
                    return 'Diselesaikan';
                case 'Closed':
                    return 'Di tutup';
                case 'Rejected':
                    return 'Dikembalikan';
                default:
                    return status || '';
            }
        },

        statusUi(status) {
            switch (status) {
                case 'Reported':
                    return { bg: 'bg-slate-50', text: 'text-slate-700', dot: 'bg-slate-400' };
                case 'Assigned':
                    return { bg: 'bg-purple-50', text: 'text-purple-700', dot: 'bg-purple-500' };
                case 'In Progress':
                    return { bg: 'bg-amber-50', text: 'text-amber-700', dot: 'bg-amber-500' };
                case 'Testing':
                    return { bg: 'bg-blue-50', text: 'text-blue-700', dot: 'bg-blue-500' };
                case 'Resolved':
                    return { bg: 'bg-emerald-50', text: 'text-emerald-700', dot: 'bg-emerald-500' };
                case 'Closed':
                    return { bg: 'bg-gray-50', text: 'text-gray-700', dot: 'bg-gray-500' };
                case 'Rejected':
                    return { bg: 'bg-red-50', text: 'text-red-700', dot: 'bg-red-500' };
                default:
                    return { bg: 'bg-slate-50', text: 'text-slate-700', dot: 'bg-slate-400' };
            }
        },

        openAssignConfirm() {
            if (!this.canAssign || !this.assigneeId || this.submitting) return;

            if (this.bug?.status === 'Reported' && !this.priority?.id) {
                const msg = 'Tentukan prioritas terlebih dahulu sebelum menugaskan programmer.';
                this.error = msg;
                this.dispatchToast('error', msg);
                return;
            }

            const actionLabel =
                (this.bug?.status === 'Assigned' && this.assigneeName !== '—')
                    ? 'Ganti programmer'
                    : 'Tugaskan';

            window.dispatchEvent(
                new CustomEvent('pm-open-assignment-confirm', {
                    detail: {
                        assigneeName: (this.assigneeNameSelected || this.selectedName() || '').trim(),
                        actionLabel,
                        ticket: this.bug?.ticket || '',
                        bugTitle: this.bug?.title || '',
                    },
                })
            );
        },

        async confirmAssign() {
            this.error = '';

            if (!this.canAssign) {
                this.error = 'Penugasan dikunci karena status bug sudah berubah.';
                return;
            }

            let ok = false;
            if (!this.assigneeId) return;
            if (this.submitting) return;
            this.submitting = true;

            try {
                const { res, data } = await this.postJson(this.assignUrl, {
                    assignee_id: parseInt(this.assigneeId, 10),
                });

                if (!res.ok) {
                    this.error =
                        data?.errors?.assignee_id?.[0] ||
                        data?.message ||
                        'Gagal melakukan penugasan.';
                    this.dispatchToast('error', this.error);
                    return;
                }

                ok = true;

                this.applyBugPayload(data);
                this.dispatchToast('success', data?.message || 'Penugasan berhasil.');
                // close modal
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'pm-confirm-assignment' }));
            } catch (e) {
                this.error = 'Gagal melakukan penugasan. Coba lagi.';
                this.dispatchToast('error', this.error);
            } finally {
                this.submitting = false;

                // Let modal UI (spinner) recover even if request fails.
                window.dispatchEvent(new CustomEvent('pm-assignment-finished', { detail: { ok } }));
            }
        },

        openUnassignConfirm() {
            if (this.submitting) return;
            window.dispatchEvent(
                new CustomEvent('pm-open-unassign-confirm', {
                    detail: {
                        ticket: this.bug?.ticket || '',
                        bugTitle: this.bug?.title || '',
                        assigneeName: this.assigneeName || '',
                    },
                })
            );
        },

        async confirmUnassign() {
            this.error = '';
            if (this.submitting) return;
            this.submitting = true;

            let ok = false;

            try {
                const { res, data } = await this.postJson(this.unassignUrl, {});
                if (!res.ok) {
                    this.error = data?.message || 'Gagal membatalkan penugasan.';
                    this.dispatchToast('error', this.error);
                    return;
                }

                ok = true;

                this.applyBugPayload(data);
                this.assigneeId = '';
                this.dispatchToast('success', data?.message || 'Penugasan dibatalkan.');
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'pm-confirm-unassign' }));
            } catch (e) {
                this.error = 'Gagal membatalkan penugasan. Coba lagi.';
                this.dispatchToast('error', this.error);
            } finally {
                this.submitting = false;

                window.dispatchEvent(new CustomEvent('pm-unassign-finished', { detail: { ok } }));
            }
        },
    };
};

Alpine.start();

// Optional helper for x-cloak
document.addEventListener('alpine:init', () => {
    // no-op (keeps file non-empty for future Alpine components)
});

// Flatpickr (shared UI)
// Used for PM Performance date filters (and can be reused elsewhere).
document.addEventListener('DOMContentLoaded', () => {
    const nodes = document.querySelectorAll('[data-flatpickr]');
    if (!nodes || nodes.length === 0) return;

    nodes.forEach((el) => {
        flatpickr(el, {
            dateFormat: 'Y-m-d',
            altInput: true,
            // UX: tampilkan format tanggal yang natural bagi user Indonesia.
            // Field asli tetap ISO agar aman untuk request GET & parsing backend.
            altFormat: 'd M Y',
            altInputClass: el.className,
            locale: Indonesian,
            allowInput: true,
            // Keep it simple: no time picker.
        });
    });

    // Optional: click on calendar icon opens the picker.
    document.querySelectorAll('[data-flatpickr-open]')?.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const id = btn.getAttribute('data-flatpickr-open');
            const input = id ? document.getElementById(id) : null;
            if (input && input._flatpickr) {
                input._flatpickr.open();
            }
        });
    });
});

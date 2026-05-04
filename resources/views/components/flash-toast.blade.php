@php
    // Global toast (fixed overlay).
    // Supports:
    // - Session flashes: error/status/validation
    // - Runtime events: window.dispatchEvent(new CustomEvent('app-toast', { detail: { type, message } }))
    // Priority: error (explicit) > validation error > status (success)
    $initialType = null;
    $initialMessage = null;

    if (session('error')) {
        $initialType = 'error';
        $initialMessage = (string) session('error');
    } elseif ($errors->any()) {
        $initialType = 'error';
        $initialMessage = (string) $errors->first();
    } elseif (session('status')) {
        $initialType = 'success';
        $initialMessage = (string) session('status');
    }

    $topOffsetClass = $topOffsetClass ?? 'top-20';
@endphp

<div
    class="fixed {{ $topOffsetClass }} right-6 z-[70] w-[calc(100vw-3rem)] max-w-md"
    x-data="{
        show: false,
        type: @js($initialType),
        message: @js($initialMessage),
        timer: null,
        open(t, m) {
            this.type = t || 'success';
            this.message = m || '';
            if (!this.message) return;
            this.show = true;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.show = false, 3200);
        },
        close() {
            this.show = false;
            clearTimeout(this.timer);
        }
    }"
    x-init="
        if (type && message) { open(type, message) }
        window.addEventListener('app-toast', (e) => {
            const t = e?.detail?.type;
            const m = e?.detail?.message;
            open(t, m);
        });
    "
    x-show="show"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    style="display: none"
    role="status"
    aria-live="polite"
>
    <div
        class="rounded-xl border px-4 py-3 shadow-lg backdrop-blur-sm"
        x-bind:class="type === 'success'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-900'
            : 'border-rose-200 bg-rose-50 text-rose-900'"
    >
        <div class="flex items-start gap-3">
            <div class="mt-0.5">
                <template x-if="type === 'success'">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">✓</span>
                </template>
                <template x-if="type !== 'success'">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-700">!</span>
                </template>
            </div>
            <div class="min-w-0">
                <p class="ui-text-alert-title" x-text="type === 'success' ? 'Berhasil' : 'Gagal'"></p>
                <p class="ui-text-alert-message mt-0.5 break-words" x-text="message"></p>
            </div>
            <button
                type="button"
                class="ml-auto text-slate-500 hover:text-slate-700"
                @click="close()"
                aria-label="Tutup"
                title="Tutup"
            >
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </div>
    </div>
</div>

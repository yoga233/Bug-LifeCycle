{{--
    Quick reference for semantic typography tokens.
    Usage examples:
    <x-typography-guide />
--}}

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="ui-text-section-title">Typography Guide</h2>
    <p class="mt-1 ui-text-body">Gunakan <code class="ui-text-mono">&lt;x-typography /&gt;</code> agar style font konsisten di seluruh sistem.</p>

    <div class="mt-5 grid gap-4 md:grid-cols-2">
        <div>
            <p class="ui-text-overline">Heading</p>
            <x-typography as="p" type="display">Display Title</x-typography>
            <x-typography as="p" type="page-title">Page Title</x-typography>
            <x-typography as="p" type="section-title">Section Title</x-typography>
            <x-typography as="p" type="heading">Card Heading</x-typography>
            <x-typography as="p" type="subheading">Subheading / lead text</x-typography>
        </div>

        <div>
            <p class="ui-text-overline">Body</p>
            <x-typography as="p" type="body">Body text untuk informasi ringkas.</x-typography>
            <x-typography as="p" type="paragraph">Paragraph text untuk kalimat/deskripsi panjang agar nyaman dibaca.</x-typography>
            <x-typography as="p" type="caption">Caption / metadata kecil.</x-typography>
            <x-typography as="p" type="label">Form label atau tabel label.</x-typography>
            <x-typography as="p" type="mono">BUG-9Z1X2Y • Monospaced reference</x-typography>
        </div>

        <div>
            <p class="ui-text-overline">State & Feedback</p>
            <x-typography as="p" type="tooltip">Tooltip text — panduan singkat untuk elemen UI.</x-typography>
            <x-typography as="p" type="alert-title">Alert title</x-typography>
            <x-typography as="p" type="alert-message">Alert message yang lebih detail.</x-typography>
            <x-typography as="p" type="placeholder">Placeholder contoh: Masukkan nomor tiket…</x-typography>
            <x-typography as="a" type="link" href="#">Pranala Aksi</x-typography>
        </div>

        <div>
            <p class="ui-text-overline">Brand</p>
            <x-typography as="p" type="brand-wordmark">PRANALA <span class="text-blue-700">BLMS</span></x-typography>
            <x-typography as="p" type="brand-tagline">Bug Lifecycle Management System</x-typography>
        </div>
    </div>
</div>

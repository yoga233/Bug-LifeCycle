import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                ui: ['Inter', ...defaultTheme.fontFamily.sans],
                heading: ['Manrope', ...defaultTheme.fontFamily.sans],
                brand: ['Manrope', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                // ═══════════════════════════════════════════════
                //  BRAND — Identitas utama aplikasi (#8a0b4e)
                //  Penggunaan: tombol, link, header, gradien
                // ═══════════════════════════════════════════════
                brand: {
                    DEFAULT: '#8a0b4e',   // ★ Primary brand — warna paling utama
                    hover:   '#6d0940',   // Hover state tombol / link
                    dark:    '#6f083f',   // Hover gelap alternatif
                    darkest: '#3a021f',   // Background paling gelap (hero/footer)
                    light:   '#b01567',   // Variasi lebih terang
                    soft:    '#b23a73',   // Gradient end / hover terang
                    tint:    '#f5e8ef',   // Background pastel / ring soft
                    'input-fill': '#fdf2f8', // Background input saat terisi
                },

                // ═══════════════════════════════════════════════
                //  SURFACE — Background & Border netral
                //  Menggantikan campuran slate/gray yang duplikat
                // ═══════════════════════════════════════════════
                surface: {
                    DEFAULT:        '#ffffff',   // Background utama halaman
                    soft:           '#f8fafc',   // slate-50  — card ringan, page bg
                    muted:          '#f1f5f9',   // slate-100 — zebra row, hover bg
                    border:         '#e2e8f0',   // slate-200 — border default (207×)
                    'border-strong':'#cbd5e1',   // slate-300 — border input form
                    mid:            '#94a3b8',   // slate-400 — elemen disabled bg
                    dark:           '#64748b',   // slate-500 — elemen abu-abu
                    darker:         '#475569',   // slate-600 — background medium
                    heavy:          '#334155',   // slate-700 — dark section
                    deepDark:       '#1e293b',   // slate-800 — bg sangat gelap
                    night:          '#0f172a',   // slate-900 — bg paling gelap
                },

                // ═══════════════════════════════════════════════
                //  CONTENT — Hierarki warna teks
                //  Menggantikan campuran text-slate-* / text-gray-*
                // ═══════════════════════════════════════════════
                content: {
                    DEFAULT:     '#1e293b',   // slate-800 — body text default (82×)
                    heading:     '#0f172a',   // slate-900 — heading / judul (89×)
                    body:        '#334155',   // slate-700 — body paragraf (119×)
                    sub:         '#475569',   // slate-600 — subheading, label (68×)
                    muted:       '#64748b',   // slate-500 — caption, hint (197×)
                    placeholder: '#94a3b8',   // slate-400 — placeholder text (193×)
                    faint:       '#cbd5e1',   // slate-300 — icon disabled (44×)
                    inverse:     '#ffffff',   // Teks di background gelap
                },

                // ═══════════════════════════════════════════════
                //  STATUS — Warna feedback / notifikasi
                // ═══════════════════════════════════════════════

                // ✅ Sukses (menggabungkan emerald + green)
                success: {
                    DEFAULT: '#10b981',   // emerald-500 — badge/icon sukses
                    light:   '#ecfdf5',   // emerald-50  — bg alert sukses
                    'light-2':'#d1fae5',  // emerald-100 — bg sukses ringan
                    text:    '#059669',   // emerald-600 — teks sukses
                    dark:    '#047857',   // emerald-700 — teks sukses gelap
                    border:  '#a7f3d0',   // emerald-200 — border sukses
                    ring:    '#10b981',   // emerald-500 — focus ring
                },

                // 🔴 Danger (menggabungkan rose + red)
                danger: {
                    DEFAULT: '#f43f5e',   // rose-500  — badge danger
                    light:   '#fff1f2',   // rose-50   — bg alert error
                    'light-2':'#ffe4e6',  // rose-100  — bg error ringan
                    text:    '#e11d48',   // rose-600  — teks error utama
                    dark:    '#be123c',   // rose-700  — teks danger gelap
                    border:  '#fecdd3',   // rose-200  — border error card
                    'border-strong':'#fda4af', // rose-300 — border error kuat
                    ring:    '#f43f5e',   // rose-500  — focus ring error
                    hard:    '#dc2626',   // red-600   — tombol hapus / kritis
                    'hard-dark':'#b91c1c',// red-700   — hover tombol hapus
                },

                // ⚠️ Warning (menggabungkan amber + yellow)
                warning: {
                    DEFAULT: '#f59e0b',   // amber-500 — badge warning
                    light:   '#fffbeb',   // amber-50  — bg alert warning
                    'light-2':'#fef3c7',  // amber-100 — bg warning ringan
                    text:    '#d97706',   // amber-600 — teks warning
                    dark:    '#b45309',   // amber-700 — teks warning gelap
                    border:  '#fde68a',   // amber-200 — border warning
                    ring:    '#f59e0b',   // amber-500 — focus ring
                },

                // ℹ️ Info (palet biru untuk link & info)
                info: {
                    DEFAULT: '#3b82f6',   // blue-500  — badge info
                    light:   '#eff6ff',   // blue-50   — bg info card
                    'light-2':'#dbeafe',  // blue-100  — bg info ringan
                    text:    '#2563eb',   // blue-600  — teks link aktif
                    dark:    '#1d4ed8',   // blue-700  — teks link
                    'text-hover':'#1e40af',// blue-800 — hover link
                    border:  '#bfdbfe',   // blue-200  — border info
                    ring:    '#3b82f6',   // blue-500  — focus ring
                },

                // ═══════════════════════════════════════════════
                //  CATEGORY — Badge / label kategori
                // ═══════════════════════════════════════════════
                'cat-sky': {
                    DEFAULT: '#0ea5e9',   // sky-500
                    light:   '#e0f2fe',   // sky-100
                    bg:      '#f0f9ff',   // sky-50
                    text:    '#0369a1',   // sky-700
                },
                'cat-violet': {
                    DEFAULT: '#8b5cf6',   // violet-500
                    light:   '#ede9fe',   // violet-100
                    bg:      '#f5f3ff',   // violet-50
                    text:    '#6d28d9',   // violet-700
                },
                'cat-purple': {
                    DEFAULT: '#9333ea',   // purple-600
                    light:   '#f3e8ff',   // purple-100
                    bg:      '#faf5ff',   // purple-50
                    text:    '#6b21a8',   // purple-800
                },
            },
        },
    },

    plugins: [forms],
};

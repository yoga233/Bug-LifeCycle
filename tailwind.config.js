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
                // Maritime-Tech Theme - Deep Plum/Magenta Palette
                primary: {
                    DEFAULT: '#5C1D4A', // Deep Plum - Primary Dark
                    light: '#7A2762', // Lighter shade for gradients
                    dark: '#4A1740', // Darker shade
                },
                accent: {
                    DEFAULT: '#D982A1', // Muted Rose/Soft Pink
                    light: '#E8A3BC', // Lighter pink
                    dark: '#C76B89', // Darker rose
                },
                // Badge backgrounds
                badge: {
                    DEFAULT: '#F4E1E8', // Very Pale Pink
                },
                // Alternate dark section
                darkSection: {
                    DEFAULT: '#3A2B3A', // Dark Muted Purple/Charcoal
                },
                // Text colors
                text: {
                    light: '#2E1E2A', // Very Dark Plum - for light backgrounds
                    dark: '#FFFFFF', // White - for dark backgrounds
                    muted: '#E0D5DC', // Light grayish pink - for subtitles on dark
                },
                // Pranala DT Brand Colors - Maritime Technology Theme (legacy)
                brand: {
                    50: '#f0f9ff',
                    100: '#e0f2fe',
                    200: '#bae6fd',
                    300: '#7dd3fc',
                    400: '#38bdf8',
                    500: '#0ea5e9',
                    600: '#0284c7',
                    700: '#0369a1',
                    800: '#075985',
                    900: '#0c4a6e', // Primary dark navy - main brand color
                    950: '#082f49',
                },
                accent: {
                    50: '#ecfeff',
                    100: '#cffafe',
                    200: '#a5f3fc',
                    300: '#67e8f9',
                    400: '#22d3ee',
                    500: '#06b6d4', // Cyan/turquoise accent
                    600: '#0891b2',
                    700: '#0e7490',
                    800: '#155e75',
                    900: '#164e63',
                },
                navy: {
                    DEFAULT: '#0c4a6e',
                    light: '#1e3a5f',
                    dark: '#082f49',
                },
            },
        },
    },

    plugins: [forms],
};

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/bootstrap.js',
                'resources/js/portal-report-success-i18n.js',
                'resources/css/portal-landing.css',
                'resources/css/portal-report.css',
                'resources/css/portal-tracking.css',
                'resources/js/portal-landing.js',
                'resources/js/portal-report-i18n.js',
                'resources/js/portal-report-annotator.js',
                'resources/js/portal-tracking-i18n.js',

            ],
            refresh: true,
        }),
    ],
});

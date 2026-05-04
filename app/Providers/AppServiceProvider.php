<?php

namespace App\Providers;

use App\Models\Notification;
use App\Models\Priority;
use App\Models\Severity;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Build topbar notification payload in a single query.
     *
     * We only need:
     * - exact unread count
     * - latest unread preview (max 5)
     *
     * Query pattern:
     * - main query fetches preview rows (LIMIT 5)
     * - scalar subquery returns unread total for current user
     */
    private function resolveTopbarNotificationsForCurrentUser(): array
    {
        $count = 0;
        $preview = collect();

        if (! auth()->check()) {
            return [$count, $preview];
        }

        $userId = (int) auth()->id();

        $rows = Notification::query()
            ->fromSub(function ($query) use ($userId) {
                $query
                    ->from('notifications as n')
                    ->selectRaw('n.id, n.user_id, n.related_id, n.type, n.message, n.is_read, n.created_at')
                    ->selectRaw('ROW_NUMBER() OVER (ORDER BY n.created_at DESC, n.id DESC) as rn')
                    ->selectRaw('COUNT(*) OVER() as unread_total')
                    ->where('n.user_id', $userId)
                    ->where('n.is_read', false);
            }, 'nq')
            ->where('rn', '<=', 5)
            ->orderBy('rn')
            ->get();

        $count = (int) ($rows->first()->unread_total ?? 0);
        $preview = $rows->map(function ($row) {
            $notification = new Notification;
            $notification->forceFill([
                'id' => (int) $row->id,
                'user_id' => (int) $row->user_id,
                'related_id' => (int) $row->related_id,
                'type' => (string) $row->type,
                'message' => (string) $row->message,
                'is_read' => (bool) $row->is_read,
                'created_at' => $row->created_at,
            ]);
            $notification->exists = true;

            return $notification;
        });

        return [$count, $preview];
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register cached priority/severity loaders for reuse across controllers
        $this->app->singleton('cached_priorities', function () {
            return Cache::remember('priorities:all', 3600, function () {
                return Priority::query()
                    ->orderBy('level')
                    ->orderBy('sla_hours')
                    ->get(['id', 'level', 'sla_hours', 'bg_color', 'text_color']);
            });
        });

        $this->app->singleton('cached_severities', function () {
            return Cache::remember('severities:all', 3600, function () {
                return Severity::query()
                    ->orderBy('level')
                    ->get(['id', 'level', 'bg_color', 'text_color']);
            });
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register scheduled task for cleaning up expired sessions
        // Runs daily at 2:00 AM
        if ($this->app->runningInConsole()) {
            $this->app->booting(function () {
                $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
                $schedule->command('sessions:cleanup')->dailyAt('02:00');
            });
        }

        Password::defaults(function (): Password {
            $rule = Password::min(12)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();

            return app()->isProduction()
                ? $rule->uncompromised()
                : $rule;
        });

        if (app()->isProduction() || (bool) env('APP_FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }

        // Public client portal language preference (EN/ID)
        // is stored in session so each page can render with consistent
        // initial toggle state before frontend i18n script runs.
        View::composer('portal.client.*', function ($view) {
            $lang = session('client_portal_lang', 'en');
            $lang = in_array($lang, ['id', 'en'], true) ? $lang : 'en';

            $view->with('clientPortalLang', $lang);
        });

        // Share unread notification count for programmer topbar icon.
        // Scoped only to the programmer layout to avoid extra queries elsewhere.
        View::composer('layouts.programmer', function ($view) {
            [$count, $preview] = $this->resolveTopbarNotificationsForCurrentUser();

            $view->with('programmerUnreadNotifications', $count);
            $view->with('programmerNotificationPreview', $preview);
        });

        // Share unread notification count for Project Manager topbar icon.
        // Scoped only to the project-manager layout to avoid extra queries elsewhere.
        View::composer('layouts.project-manager', function ($view) {
            [$count, $preview] = $this->resolveTopbarNotificationsForCurrentUser();

            $view->with('pmUnreadNotifications', $count);
            $view->with('pmNotificationPreview', $preview);
        });

        // Share unread notification count for QA topbar icon.
        // Scoped only to the QA layout to avoid extra queries elsewhere.
        View::composer('layouts.qa', function ($view) {
            [$count, $preview] = $this->resolveTopbarNotificationsForCurrentUser();

            $view->with('qaUnreadNotifications', $count);
            $view->with('qaNotificationPreview', $preview);
        });
    }
}

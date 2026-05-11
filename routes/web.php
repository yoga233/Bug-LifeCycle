<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectManager\MasterDataController;
use App\Http\Controllers\ProjectManager\TeamManagementController;
use App\Http\Controllers\ProjectManager\ManagementController;
use App\Http\Controllers\ProjectManager\OverviewController;
use App\Http\Controllers\ProjectManager\IssueController;
use App\Http\Controllers\ProjectManager\BugAssignmentController;
use App\Http\Controllers\ProjectManager\IssueCommentController;
use App\Http\Controllers\ProjectManager\PerformanceController as ProjectManagerPerformanceController;
use App\Http\Controllers\ProjectManager\NotificationController as ProjectManagerNotificationController;
use App\Http\Controllers\ProjectManager\GuestReportQueueController;
use App\Http\Controllers\Client\BugReportController;
use App\Http\Controllers\Client\BugTrackingController;
use App\Http\Controllers\Client\ClientPortalLanguageController;
use App\Http\Controllers\Programmer\BugWorkflowController;
use App\Http\Controllers\Programmer\BugCommentController;
use App\Http\Controllers\Programmer\DashboardController as ProgrammerDashboardController;
use App\Http\Controllers\Programmer\NotificationController as ProgrammerNotificationController;
use App\Http\Controllers\Programmer\PerformanceController as ProgrammerPerformanceController;
use App\Http\Controllers\QA\BugValidationController;
use App\Http\Controllers\QA\BugController as QABugController;
use App\Http\Controllers\QA\BugCommentController as QABugCommentController;
use App\Http\Controllers\QA\TestingQueueController;
use App\Http\Controllers\QA\NotificationController as QANotificationController;
use Illuminate\Support\Facades\Route;

// Default entrypoint (setelah `php artisan serve`)
// - Guest  -> halaman login internal
// - Auth   -> masuk ke dashboard (role-based redirect di route /dashboard)
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

// Public client portal (tanpa login) - menggunakan guest layout untuk performa optimal
// Catatan: kita TIDAK menghapus middleware session karena komponen topbar/blade
// menggunakan @auth yang membutuhkan session. Jika session tidak ada, akan error.
// Solusi: biarkan session berjalan normal (tidak ada withoutMiddleware).
Route::view('/portal', 'portal.landing.index')->name('client.landing');
Route::get('/report', [BugReportController::class, 'create'])->name('client.report');
Route::post('/report', [BugReportController::class, 'store'])->name('client.report.store');
Route::view('/report/success', 'portal.report.success')->name('client.report.success');
Route::get('/track', [BugTrackingController::class, 'show'])->name('client.tracking');
Route::post('/portal/language', [ClientPortalLanguageController::class, 'store'])->name('client.language.store');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if (! $user) {
        return redirect()->route('login');
    }

    // Unified internal dashboard entrypoint (role-based)
    if ($user->hasRole('Project Manager')) {
        return redirect()->route('pm.dashboard');
    }

    if ($user->hasRole('Programmer')) {
        return redirect()->route('programmer.dashboard');
    }

    if ($user->hasRole('QA')) {
        return redirect()->route('bugs.testing-queue');
    }

    return redirect()->route('profile.edit');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/bugs/testing-queue', function () {
    return redirect()->route('qa.testing-queue');
})->middleware(['auth', 'verified', 'role:QA'])->name('bugs.testing-queue');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('project-manager')
        ->name('pm.')
        ->middleware(['verified', 'role:Project Manager'])
        ->group(function () {
            // Admin/PM Dashboard utama - dengan caching untuk statistik
            Route::get('/dashboard', [OverviewController::class, 'index'])->name('dashboard')->middleware('cache.control:300,private');

            // Legacy URL -> keep old links working
            Route::get('/overview', fn () => redirect()->route('pm.dashboard'))->name('overview');
            Route::get('/issues', [IssueController::class, 'index'])->name('issues.index');
            Route::get('/issues/{bug}', [IssueController::class, 'show'])->name('issues.show');

            Route::get('/notifications', [ProjectManagerNotificationController::class, 'index'])->name('notifications');
            Route::post('/notifications/mark-all-read', [ProjectManagerNotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
            Route::post('/notifications/{notification}/read', [ProjectManagerNotificationController::class, 'markRead'])->name('notifications.read');
            Route::delete('/notifications/{notification}', [ProjectManagerNotificationController::class, 'destroy'])->name('notifications.destroy');

            Route::post('/issues/{bug}/assign', [BugAssignmentController::class, 'assign'])->name('issues.assign');
            Route::post('/issues/{bug}/unassign', [BugAssignmentController::class, 'unassign'])->name('issues.unassign');
            Route::post('/issues/{bug}/priority', [BugAssignmentController::class, 'updatePriority'])->name('issues.priority.update');

            Route::post('/issues/{bug}/comments', [IssueCommentController::class, 'store'])->name('issues.comments.store');
            // Single hub page
            Route::get('/management', [ManagementController::class, 'index'])->name('management');

            // Legacy pages -> redirect to hub with tab
            Route::get('/team', fn () => redirect()->route('pm.management', ['tab' => 'team']))->name('team');
            Route::get('/kinerja', [ProjectManagerPerformanceController::class, 'index'])->name('kinerja');

            // Modal actions on project-manager/team (rendered via Management hub)
            Route::post('/team/users', [TeamManagementController::class, 'storeUser'])->name('team.users.store');
            Route::put('/team/users/{user}', [TeamManagementController::class, 'updateUser'])->name('team.users.update');
            Route::delete('/team/users/{user}', [TeamManagementController::class, 'destroyUser'])->name('team.users.destroy');

            Route::post('/team/projects', [TeamManagementController::class, 'storeProject'])->name('team.projects.store');
            Route::put('/team/projects/{project}', [TeamManagementController::class, 'updateProject'])->name('team.projects.update');
            Route::delete('/team/projects/{project}', [TeamManagementController::class, 'destroyProject'])->name('team.projects.destroy');

            // Master Data
            // NOTE: Projects are managed via /project-manager/management?tab=team (modal).
            // Route::resource('projects', ProjectController::class)->except(['show']);
            Route::get('/master-data', fn () => redirect()->route('pm.management', ['tab' => 'master']))->name('master-data');

            // Modal CRUD endpoints
            Route::post('/master-data/severities', [MasterDataController::class, 'storeSeverity'])->name('master-data.severities.store');
            Route::put('/master-data/severities/{severity}', [MasterDataController::class, 'updateSeverity'])->name('master-data.severities.update');
            Route::delete('/master-data/severities/{severity}', [MasterDataController::class, 'destroySeverity'])->name('master-data.severities.destroy');

            Route::post('/master-data/priorities', [MasterDataController::class, 'storePriority'])->name('master-data.priorities.store');
            Route::put('/master-data/priorities/{priority}', [MasterDataController::class, 'updatePriority'])->name('master-data.priorities.update');
            Route::delete('/master-data/priorities/{priority}', [MasterDataController::class, 'destroyPriority'])->name('master-data.priorities.destroy');

            // Guest Report Queue (Hybrid Architecture - PM validation before bug creation)
            Route::get('/guest-reports', [GuestReportQueueController::class, 'index'])->name('guest-reports');
            Route::get('/guest-reports/{guestReport}', [GuestReportQueueController::class, 'show'])->name('guest-reports.show');
            Route::post('/guest-reports/{guestReport}/approve', [GuestReportQueueController::class, 'approve'])->name('guest-reports.approve');
            Route::post('/guest-reports/{guestReport}/reject', [GuestReportQueueController::class, 'reject'])->name('guest-reports.reject');
            Route::post('/guest-reports/bulk-approve', [GuestReportQueueController::class, 'bulkApprove'])->name('guest-reports.bulk-approve');
            Route::post('/guest-reports/bulk-reject', [GuestReportQueueController::class, 'bulkReject'])->name('guest-reports.bulk-reject');

            // Legacy routes removed (now unified in /project-manager/management?tab=master)
            // Route::resource('severities', SeverityController::class)->except(['show']);
            // Route::resource('priorities', PriorityController::class)->except(['show']);
        });

    // Programmer area
    Route::prefix('programmer')
        ->name('programmer.')
        ->middleware(['verified', 'role:Programmer'])
        ->group(function () {
            Route::get('/dashboard', [ProgrammerDashboardController::class, 'index'])->name('dashboard');
            Route::get('/notifications', [ProgrammerNotificationController::class, 'index'])->name('notifications');
            Route::post('/notifications/mark-all-read', [ProgrammerNotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
            Route::post('/notifications/{notification}/read', [ProgrammerNotificationController::class, 'markRead'])->name('notifications.read');
            Route::delete('/notifications/{notification}', [ProgrammerNotificationController::class, 'destroy'])->name('notifications.destroy');
            Route::get('/kinerja', [ProgrammerPerformanceController::class, 'index'])->name('kinerja');

            Route::get('/bugs/{bug}', [ProgrammerDashboardController::class, 'show'])->name('bugs.show');
            Route::post('/bugs/{bug}/comments', [BugCommentController::class, 'store'])->name('bugs.comments.store');

            Route::post('/bugs/{bug}/start', [BugWorkflowController::class, 'start'])->name('bugs.start');
            Route::post('/bugs/{bug}/send-to-testing', [BugWorkflowController::class, 'sendToTesting'])->name('bugs.sendToTesting');
        });

    // QA area
    Route::prefix('qa')
        ->name('qa.')
        ->middleware(['verified', 'role:QA'])
        ->group(function () {
            Route::get('/testing-queue', [TestingQueueController::class, 'index'])->name('testing-queue');

            Route::get('/notifications', [QANotificationController::class, 'index'])->name('notifications');
            Route::post('/notifications/mark-all-read', [QANotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
            Route::delete('/notifications/{notification}', [QANotificationController::class, 'destroy'])->name('notifications.destroy');
            Route::post('/notifications/{notification}/read', [QANotificationController::class, 'markRead'])->name('notifications.read');

            Route::get('/bugs/{bug}', [QABugController::class, 'show'])->name('bugs.show');
            Route::post('/bugs/{bug}/comments', [QABugCommentController::class, 'store'])->name('bugs.comments.store');

            Route::post('/bugs/{bug}/approve', [BugValidationController::class, 'approve'])->name('bugs.approve');
            Route::post('/bugs/{bug}/reject', [BugValidationController::class, 'reject'])->name('bugs.reject');
        });

});

require __DIR__.'/auth.php';

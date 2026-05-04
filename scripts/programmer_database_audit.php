<?php

declare(strict_types=1);

/**
 * PROGRAMMER DATABASE AUDIT SCRIPT
 * 
 * Performs comprehensive database query audit for all pages accessible by Programmer role.
 * 
 * Usage: php scripts/programmer_database_audit.php
 * 
 * Target role: Programmer
 * Routes tested: programmer.dashboard, programmer.notifications, programmer.kinerja, 
 *                programmer.bugs.show, programmer.bugs.comments.store, 
 *                programmer.bugs.start, programmer.bugs.sendToTesting,
 *                profile.edit, profile.update
 */

use App\Models\Bug;
use App\Models\BugStatusHistory;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Severity;
use App\Models\User;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

/** @var HttpKernel $kernel */
$kernel = $app->make(HttpKernel::class);

function routePath(string $name, array $parameters = []): string
{
    return route($name, $parameters, false);
}

function interpolateQuery(string $sql, array $bindings): string
{
    $segments = explode('?', $sql);
    $result = '';

    foreach ($segments as $i => $segment) {
        $result .= $segment;

        if ($i < count($bindings)) {
            $binding = $bindings[$i];

            if ($binding instanceof DateTimeInterface) {
                $value = "'".$binding->format('Y-m-d H:i:s')."'";
            } elseif (is_bool($binding)) {
                $value = $binding ? '1' : '0';
            } elseif (is_numeric($binding)) {
                $value = (string) $binding;
            } elseif ($binding === null) {
                $value = 'null';
            } else {
                $escaped = str_replace("'", "''", (string) $binding);
                $value = "'{$escaped}'";
            }

            $result .= $value;
        }
    }

    return $result;
}

function normalizeSqlSignature(string $sql): string
{
    $normalized = preg_replace('/\s+/', ' ', trim(strtolower($sql)));

    return $normalized ?? strtolower(trim($sql));
}

function extractTablesFromSql(string $sql): array
{
    $tables = [];
    if (preg_match_all('/\b(from|join|update|into)\s+`?([a-zA-Z0-9_]+)`?/i', $sql, $matches)) {
        foreach ($matches[2] as $table) {
            $tables[] = strtolower($table);
        }
    }

    return array_values(array_unique($tables));
}

function countReturnedRows(string $sql, array $bindings): ?int
{
    if (! preg_match('/^\s*select\b/i', $sql)) {
        return null;
    }

    try {
        return count(DB::select($sql, $bindings));
    } catch (Throwable) {
        return null;
    }
}

function safeRollback(): void
{
    try {
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
    } catch (Throwable) {
        try {
            DB::disconnect();
            DB::reconnect();
        } catch (Throwable) {
            // no-op: best effort cleanup to keep audit process running
        }
    }
}

/**
 * @param  callable():array{method:string,uri:string,payload?:array<string,mixed>}  $builder
 * @return array<string,mixed>
 */
function auditScenario(string $name, callable $builder, HttpKernel $kernel, User $programmerUser): array
{
    DB::beginTransaction();

    $responseStatus = null;
    $responseLocation = null;
    $queryLog = [];
    $error = null;
    $requestMeta = [
        'method' => 'GET',
        'uri' => '',
        'payload' => [],
    ];

    try {
        $scenario = $builder();
        $method = strtoupper((string) ($scenario['method'] ?? 'GET'));
        $uri = (string) ($scenario['uri'] ?? '/');
        $payload = is_array($scenario['payload'] ?? null) ? $scenario['payload'] : [];

        $requestMeta = [
            'method' => $method,
            'uri' => $uri,
            'payload' => $payload,
        ];

        $session = app('session')->driver();
        $session->start();

        Auth::shouldUse('web');
        Auth::guard('web')->setUser($programmerUser);

        $server = [
            'HTTP_HOST' => 'localhost',
            'HTTPS' => 'off',
        ];

        $cookies = [
            $session->getName() => $session->getId(),
        ];

        $request = Request::create($uri, $method, $payload, $cookies, [], $server);
        $request->setLaravelSession($session);
        $request->setUserResolver(fn () => $programmerUser);

        if (! in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            $token = $session->token();
            $request->request->set('_token', $token);
            $request->headers->set('X-CSRF-TOKEN', $token);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $responseStatus = $response->getStatusCode();
        $responseLocation = $response->headers->get('Location');
        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();
    } catch (Throwable $e) {
        DB::disableQueryLog();
        $error = $e::class.': '.$e->getMessage();

        try {
            DB::disconnect();
            DB::reconnect();
        } catch (Throwable) {
            // no-op
        }
    } finally {
        safeRollback();
    }

    $queryDetails = [];
    $totalTimeMs = 0.0;
    $maxQueryMs = 0.0;
    $duplicateMap = [];
    $slowQueries = [];
    $selectStar = [];
    $noLimitListQueries = [];
    $joinGtThree = [];
    $tables = [];

    foreach ($queryLog as $idx => $entry) {
        $sql = (string) ($entry['query'] ?? '');
        $bindings = is_array($entry['bindings'] ?? null) ? $entry['bindings'] : [];
        $timeMs = (float) ($entry['time'] ?? 0.0);
        $fullSql = interpolateQuery($sql, $bindings);
        $rowsReturned = countReturnedRows($sql, $bindings);

        $totalTimeMs += $timeMs;
        $maxQueryMs = max($maxQueryMs, $timeMs);

        $signature = normalizeSqlSignature($sql);
        if (! isset($duplicateMap[$signature])) {
            $duplicateMap[$signature] = [
                'sql' => $fullSql,
                'count' => 0,
            ];
        }
        $duplicateMap[$signature]['count']++;

        if ($timeMs > 20.0) {
            $slowQueries[] = [
                'index' => $idx + 1,
                'time_ms' => round($timeMs, 3),
                'sql' => $fullSql,
                'raw_sql' => $sql,
                'bindings' => $bindings,
                'rows_returned' => $rowsReturned,
            ];
        }

        if (preg_match('/^\s*select\s+((`?[a-zA-Z0-9_]+`?\.)?\*)\b/i', $sql)) {
            $selectStar[] = [
                'index' => $idx + 1,
                'sql' => $fullSql,
            ];
        }

        $isSelect = (bool) preg_match('/^\s*select\b/i', $sql);
        $hasLimit = (bool) preg_match('/\blimit\b/i', $sql);
        $looksLikeList = (bool) preg_match('/\bfrom\s+`?(bugs|notifications|users|projects|bug_status_histories|severities|priorities|comments)`?\b/i', $sql);

        if ($isSelect && $looksLikeList && ! $hasLimit) {
            $noLimitListQueries[] = [
                'index' => $idx + 1,
                'sql' => $fullSql,
            ];
        }

        $joinCount = substr_count(strtolower($sql), ' join ');
        if ($joinCount > 3) {
            $joinGtThree[] = [
                'index' => $idx + 1,
                'join_count' => $joinCount,
                'sql' => $fullSql,
            ];
        }

        $tables = array_values(array_unique(array_merge($tables, extractTablesFromSql($sql))));

        $queryDetails[] = [
            'index' => $idx + 1,
            'time_ms' => round($timeMs, 3),
            'rows_returned' => $rowsReturned,
            'sql' => $fullSql,
            'raw_sql' => $sql,
            'bindings' => $bindings,
        ];
    }

    $duplicates = [];
    foreach ($duplicateMap as $item) {
        if (($item['count'] ?? 0) > 1) {
            $duplicates[] = [
                'count' => $item['count'],
                'sql' => $item['sql'],
            ];
        }
    }

    usort($duplicates, fn (array $a, array $b) => ($b['count'] <=> $a['count']));

    return [
        'name' => $name,
        'method' => $requestMeta['method'],
        'uri' => $requestMeta['uri'],
        'payload' => $requestMeta['payload'],
        'status_code' => $responseStatus,
        'redirect_to' => $responseLocation,
        'error' => $error,
        'total_queries' => count($queryDetails),
        'total_time_ms' => round($totalTimeMs, 3),
        'max_query_time_ms' => round($maxQueryMs, 3),
        'queries' => $queryDetails,
        'slow_queries' => $slowQueries,
        'duplicates' => $duplicates,
        'select_star_queries' => $selectStar,
        'no_limit_list_queries' => $noLimitListQueries,
        'join_gt_3_queries' => $joinGtThree,
        'tables' => $tables,
    ];
}

function explainSlowQuery(string $rawSql, array $bindings): array
{
    try {
        $rows = DB::select('EXPLAIN '.$rawSql, $bindings);

        return [
            'ok' => true,
            'plan' => array_map(static function ($row) {
                return (array) $row;
            }, $rows),
        ];
    } catch (Throwable $e) {
        return [
            'ok' => false,
            'error' => $e::class.': '.$e->getMessage(),
            'plan' => [],
        ];
    }
}

function runListMetric(HttpKernel $kernel, User $programmerUser, string $uri): array
{
    $result = auditScenario(
        name: 'list_metric '.$uri,
        builder: static fn () => ['method' => 'GET', 'uri' => $uri],
        kernel: $kernel,
        programmerUser: $programmerUser,
    );

    return [
        'uri' => $uri,
        'status_code' => $result['status_code'],
        'total_queries' => $result['total_queries'],
        'total_time_ms' => $result['total_time_ms'],
        'max_query_time_ms' => $result['max_query_time_ms'],
        'slow_query_count' => count($result['slow_queries']),
    ];
}

// =============================================================================
// MAIN EXECUTION
// =============================================================================

echo "=====================================================\n";
echo "  PROGRAMMER DATABASE AUDIT SCRIPT\n";
echo "  Role: Programmer\n";
echo "  Target: 15-20+ pages/scenarios\n";
echo "=====================================================\n\n";

// Find Programmer user
/** @var User|null $programmerUser */
$programmerUser = User::query()
    ->where('is_active', true)
    ->whereHas('roles', fn ($q) => $q->where('name', 'Programmer'))
    ->first();

if (! $programmerUser) {
    fwrite(STDERR, "Tidak ditemukan user Programmer aktif. Jalankan seeder terlebih dahulu.\n");
    exit(1);
}

echo "Found Programmer user: {$programmerUser->email} (ID: {$programmerUser->id})\n\n";

// Ensure we have necessary reference data
$project = Project::query()->first() ?? Project::query()->create([
    'name' => 'Audit Project',
    'platform' => 'Web',
    'description' => 'Auto-created for DB audit',
]);

$severity = Severity::query()->first() ?? Severity::query()->create([
    'level' => 'Audit Severity',
    'description' => 'Auto-created for DB audit',
]);

$priority = Priority::query()->orderBy('sla_hours')->first() ?? Priority::query()->create([
    'level' => 'Audit Priority',
    'sla_hours' => 24,
]);

$today = Carbon::now();
$oneYearAgo = $today->copy()->subYear()->toDateString();
$todayString = $today->toDateString();

echo "Reference data ready (project: {$project->id}, severity: {$severity->id}, priority: {$priority->id})\n\n";

$scenarios = [];

// =============================================================================
// PROGRAMMER SPECIFIC SCENARIOS (15-20 pages)
// =============================================================================

// 1. Programmer Dashboard
$scenarios[] = [
    'name' => 'Programmer Dashboard',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('programmer.dashboard'),
    ],
];

// 2. Programmer Notifications Index
$scenarios[] = [
    'name' => 'Programmer Notifications Index',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('programmer.notifications'),
    ],
];

// 3. Programmer Kinerja (Performance)
$scenarios[] = [
    'name' => 'Programmer Kinerja',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('programmer.kinerja'),
    ],
];

// 4. Programmer Kinerja with date filter (1 year)
$scenarios[] = [
    'name' => 'Programmer Kinerja (1 Year Filter)',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('programmer.kinerja', [
            'from' => $oneYearAgo,
            'to' => $todayString,
        ]),
    ],
];

// 5. Programmer Bug Show (assigned bug detail)
$scenarios[] = [
    'name' => 'Programmer Bug Show',
    'builder' => static function () use ($project, $severity, $priority, $programmerUser): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $programmerUser->id,
            'guest_name' => 'Audit Guest',
            'guest_email' => 'audit-show-prog@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Audit show bug programmer',
            'description' => 'Audit detail bug for programmer',
            'frequency' => 'Often',
            'status' => 'Assigned',
        ]);

        return [
            'method' => 'GET',
            'uri' => routePath('programmer.bugs.show', ['bug' => $bug->id]),
        ];
    },
];

// 6. Programmer Bug Show with comments
$scenarios[] = [
    'name' => 'Programmer Bug Show with Comments',
    'builder' => static function () use ($project, $severity, $priority, $programmerUser): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $programmerUser->id,
            'guest_name' => 'Audit Guest Comments',
            'guest_email' => 'audit-comments@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Bug with comments',
            'description' => 'Bug for comment test',
            'frequency' => 'Often',
            'status' => 'In Progress',
        ]);

        // Add some comments
        Comment::query()->insert([
            ['bug_id' => $bug->id, 'user_id' => $programmerUser->id, 'content' => 'Comment 1', 'created_at' => now()],
            ['bug_id' => $bug->id, 'user_id' => $programmerUser->id, 'content' => 'Comment 2', 'created_at' => now()],
        ]);

        return [
            'method' => 'GET',
            'uri' => routePath('programmer.bugs.show', ['bug' => $bug->id]),
        ];
    },
];

// 7. Programmer Bug Comment Store
$scenarios[] = [
    'name' => 'Programmer Bug Comment Store',
    'builder' => static function () use ($project, $severity, $priority, $programmerUser): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $programmerUser->id,
            'guest_name' => 'Comment Guest',
            'guest_email' => 'comment-store@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Comment store bug',
            'description' => 'Bug for comment store test',
            'frequency' => 'Often',
            'status' => 'In Progress',
        ]);

        return [
            'method' => 'POST',
            'uri' => routePath('programmer.bugs.comments.store', ['bug' => $bug->id]),
            'payload' => ['content' => 'Komentar audit query programmer.'],
        ];
    },
];

// 8. Programmer Bug Start (transition to In Progress)
$scenarios[] = [
    'name' => 'Programmer Bug Start (In Progress)',
    'builder' => static function () use ($project, $severity, $priority, $programmerUser): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $programmerUser->id,
            'guest_name' => 'Start Guest',
            'guest_email' => 'start-bug@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Start bug',
            'description' => 'Bug to start',
            'frequency' => 'Often',
            'status' => 'Assigned',
        ]);

        return [
            'method' => 'POST',
            'uri' => routePath('programmer.bugs.start', ['bug' => $bug->id]),
        ];
    },
];

// 9. Programmer Bug Send to Testing
$scenarios[] = [
    'name' => 'Programmer Bug Send to Testing',
    'builder' => static function () use ($project, $severity, $priority, $programmerUser): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $programmerUser->id,
            'guest_name' => 'Testing Guest',
            'guest_email' => 'send-testing@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Send to testing bug',
            'description' => 'Bug ready for testing',
            'frequency' => 'Often',
            'status' => 'In Progress',
        ]);

        return [
            'method' => 'POST',
            'uri' => routePath('programmer.bugs.sendToTesting', ['bug' => $bug->id]),
        ];
    },
];

// 10. Programmer Notifications Mark All Read
$scenarios[] = [
    'name' => 'Programmer Notifications Mark All Read',
    'builder' => static function () use ($programmerUser, $project, $severity, $priority): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $programmerUser->id,
            'guest_name' => 'Notif All Read Guest',
            'guest_email' => 'notif-all-read@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Notif all read bug',
            'description' => 'Notif all read bug',
            'frequency' => 'Often',
            'status' => 'Assigned',
        ]);

        Notification::query()->insert([
            [
                'user_id' => $programmerUser->id,
                'related_id' => $bug->id,
                'type' => 'BugAssigned',
                'message' => 'Notif unread 1',
                'is_read' => false,
                'created_at' => now(),
            ],
            [
                'user_id' => $programmerUser->id,
                'related_id' => $bug->id,
                'type' => 'BugStatusChanged',
                'message' => 'Notif unread 2',
                'is_read' => false,
                'created_at' => now(),
            ],
        ]);

        return [
            'method' => 'POST',
            'uri' => routePath('programmer.notifications.markAllRead'),
        ];
    },
];

// 11. Programmer Notifications Mark Single Read
$scenarios[] = [
    'name' => 'Programmer Notifications Mark Read',
    'builder' => static function () use ($programmerUser, $project, $severity, $priority): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $programmerUser->id,
            'guest_name' => 'Notif Read Guest',
            'guest_email' => 'notif-read-prog@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Notif read bug',
            'description' => 'Notif read bug',
            'frequency' => 'Often',
            'status' => 'Assigned',
        ]);

        $notification = Notification::query()->create([
            'user_id' => $programmerUser->id,
            'related_id' => $bug->id,
            'type' => 'BugAssigned',
            'message' => 'Notif mark read',
            'is_read' => false,
            'created_at' => now(),
        ]);

        return [
            'method' => 'POST',
            'uri' => routePath('programmer.notifications.read', ['notification' => $notification->id]),
        ];
    },
];

// 12. Programmer Notifications Destroy
$scenarios[] = [
    'name' => 'Programmer Notifications Destroy',
    'builder' => static function () use ($programmerUser, $project, $severity, $priority): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $programmerUser->id,
            'guest_name' => 'Notif Destroy Guest',
            'guest_email' => 'notif-destroy-prog@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Notif destroy bug',
            'description' => 'Notif destroy bug',
            'frequency' => 'Often',
            'status' => 'Assigned',
        ]);

        $notification = Notification::query()->create([
            'user_id' => $programmerUser->id,
            'related_id' => $bug->id,
            'type' => 'BugAssigned',
            'message' => 'Notif destroy',
            'is_read' => false,
            'created_at' => now(),
        ]);

        return [
            'method' => 'DELETE',
            'uri' => routePath('programmer.notifications.destroy', ['notification' => $notification->id]),
        ];
    },
];

// 13. Profile Edit (shared by all roles)
$scenarios[] = [
    'name' => 'Profile Edit',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('profile.edit'),
    ],
];

// 14. Profile Update
$scenarios[] = [
    'name' => 'Profile Update',
    'builder' => static function (): array {
        return [
            'method' => 'PATCH',
            'uri' => routePath('profile.update'),
            'payload' => [
                'name' => 'Updated Name',
                'email' => 'updated@example.test',
            ],
        ];
    },
];

// 15. Dashboard Redirect (entry point)
$scenarios[] = [
    'name' => 'Dashboard Redirect (to programmer.dashboard)',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('dashboard'),
    ],
];

// 16. Programmer Bug Show with attachments
$scenarios[] = [
    'name' => 'Programmer Bug Show with Attachments',
    'builder' => static function () use ($project, $severity, $priority, $programmerUser): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $programmerUser->id,
            'guest_name' => 'Attachment Guest',
            'guest_email' => 'attachments@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Bug with attachments',
            'description' => 'Bug for attachment test',
            'frequency' => 'Often',
            'status' => 'In Progress',
        ]);

        // Add attachments (using Attachment model)
        $attachmentClass = 'App\\Models\\Attachment';
        if (class_exists($attachmentClass)) {
            $attachmentClass::query()->insert([
                ['bug_id' => $bug->id, 'file_name' => 'screenshot1.png', 'file_path' => '/uploads/test1.png', 'file_size' => 1024, 'mime_type' => 'image/png', 'created_at' => now()],
                ['bug_id' => $bug->id, 'file_name' => 'screenshot2.jpg', 'file_path' => '/uploads/test2.jpg', 'file_size' => 2048, 'mime_type' => 'image/jpeg', 'created_at' => now()],
            ]);
        }

        return [
            'method' => 'GET',
            'uri' => routePath('programmer.bugs.show', ['bug' => $bug->id]),
        ];
    },
];

// 17. Programmer Bug Show with status history
$scenarios[] = [
    'name' => 'Programmer Bug Show with Status History',
    'builder' => static function () use ($project, $severity, $priority, $programmerUser): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $programmerUser->id,
            'guest_name' => 'History Guest',
            'guest_email' => 'history@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Bug with history',
            'description' => 'Bug for history test',
            'frequency' => 'Often',
            'status' => 'Testing',
        ]);

        // Add status history
        BugStatusHistory::query()->insert([
            ['bug_id' => $bug->id, 'user_id' => $programmerUser->id, 'old_status' => 'Reported', 'new_status' => 'Assigned', 'changed_at' => now()->subDays(2)],
            ['bug_id' => $bug->id, 'user_id' => $programmerUser->id, 'old_status' => 'Assigned', 'new_status' => 'In Progress', 'changed_at' => now()->subDay()],
            ['bug_id' => $bug->id, 'user_id' => $programmerUser->id, 'old_status' => 'In Progress', 'new_status' => 'Testing', 'changed_at' => now()],
        ]);

        return [
            'method' => 'GET',
            'uri' => routePath('programmer.bugs.show', ['bug' => $bug->id]),
        ];
    },
];

// 18. Programmer Kinerja - empty date range
$scenarios[] = [
    'name' => 'Programmer Kinerja (Empty Range)',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('programmer.kinerja', [
            'from' => '2020-01-01',
            'to' => '2020-12-31',
        ]),
    ],
];

// 19. Programmer Kinerja - current month
$scenarios[] = [
    'name' => 'Programmer Kinerja (Current Month)',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('programmer.kinerja', [
            'from' => $today->copy()->startOfMonth()->toDateString(),
            'to' => $today->copy()->endOfMonth()->toDateString(),
        ]),
    ],
];

// 20. Programmer Dashboard with multiple pages of tasks
$scenarios[] = [
    'name' => 'Programmer Dashboard Page 2',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('programmer.dashboard', ['page' => 2]),
    ],
];

echo "Total scenarios to test: ".count($scenarios)."\n\n";

// =============================================================================
// RUN AUDIT SCENARIOS
// =============================================================================

$results = [];

foreach ($scenarios as $scenario) {
    echo "Running: {$scenario['name']}... ";
    
    $result = auditScenario(
        name: $scenario['name'],
        builder: $scenario['builder'],
        kernel: $kernel,
        programmerUser: $programmerUser,
    );
    
    $status = $result['error'] ?? ($result['status_code'] ?? 'ERROR');
    echo "[{$status}] {$result['total_queries']} queries, {$result['total_time_ms']}ms\n";
    
    $results[] = $result;
}

// =============================================================================
// ANALYZE SLOW QUERIES & INDEXES
// =============================================================================

echo "\n=====================================================\n";
echo "  ANALYZING SLOW QUERIES & INDEXES\n";
echo "=====================================================\n\n";

$globalSlow = [];
$usedTables = [];

foreach ($results as $result) {
    foreach (($result['tables'] ?? []) as $table) {
        $usedTables[$table] = true;
    }

    foreach (($result['slow_queries'] ?? []) as $slow) {
        $signature = md5(($slow['raw_sql'] ?? '').'|'.json_encode($slow['bindings'] ?? []));

        if (! isset($globalSlow[$signature])) {
            $globalSlow[$signature] = [
                'raw_sql' => $slow['raw_sql'],
                'bindings' => $slow['bindings'],
                'sample_sql' => $slow['sql'],
                'max_time_ms' => (float) $slow['time_ms'],
                'occurrences' => 1,
                'routes' => [$result['name']],
            ];
            continue;
        }

        $globalSlow[$signature]['max_time_ms'] = max(
            (float) $globalSlow[$signature]['max_time_ms'],
            (float) $slow['time_ms']
        );
        $globalSlow[$signature]['occurrences']++;
        $globalSlow[$signature]['routes'][] = $result['name'];
        $globalSlow[$signature]['routes'] = array_values(array_unique($globalSlow[$signature]['routes']));
    }
}

// Run EXPLAIN on slow queries
$slowExplains = [];
foreach ($globalSlow as $item) {
    $rawSql = (string) ($item['raw_sql'] ?? '');
    $bindings = is_array($item['bindings'] ?? null) ? $item['bindings'] : [];

    $explain = explainSlowQuery($rawSql, $bindings);

    $slowExplains[] = [
        'sql' => $item['sample_sql'],
        'max_time_ms' => round((float) $item['max_time_ms'], 3),
        'occurrences' => (int) $item['occurrences'],
        'routes' => $item['routes'],
        'explain' => $explain,
    ];
}

// Get existing indexes
$tableIndexes = [];
foreach (array_keys($usedTables) as $table) {
    try {
        $indexRows = DB::select("SHOW INDEX FROM `{$table}`");
        $indexMap = [];

        foreach ($indexRows as $row) {
            $r = (array) $row;
            $keyName = (string) ($r['Key_name'] ?? '');
            if ($keyName === '') {
                continue;
            }

            if (! isset($indexMap[$keyName])) {
                $indexMap[$keyName] = [
                    'non_unique' => (int) ($r['Non_unique'] ?? 1),
                    'columns' => [],
                ];
            }

            $indexMap[$keyName]['columns'][(int) ($r['Seq_in_index'] ?? 0)] = (string) ($r['Column_name'] ?? '');
        }

        foreach ($indexMap as &$idx) {
            ksort($idx['columns']);
            $idx['columns'] = array_values($idx['columns']);
        }

        $tableIndexes[$table] = $indexMap;
    } catch (Throwable $e) {
        $tableIndexes[$table] = [
            '_error' => $e::class.': '.$e->getMessage(),
        ];
    }
}

// =============================================================================
// EDGE CASE TESTING
// =============================================================================

echo "\n=====================================================\n";
echo "  EDGE CASE TESTING\n";
echo "=====================================================\n\n";

$edgeCase = [
    'small_dataset' => [],
    'large_dataset' => [],
    'comparison' => [],
];

// Small dataset tests
$edgeCase['small_dataset'] = [
    'dashboard_page_1' => runListMetric($kernel, $programmerUser, routePath('programmer.dashboard', ['page' => 1])),
    'dashboard_page_2' => runListMetric($kernel, $programmerUser, routePath('programmer.dashboard', ['page' => 2])),
    'notifications_page_1' => runListMetric($kernel, $programmerUser, routePath('programmer.notifications', ['page' => 1])),
    'notifications_page_3' => runListMetric($kernel, $programmerUser, routePath('programmer.notifications', ['page' => 3])),
    'kinerja_1_year' => runListMetric($kernel, $programmerUser, routePath('programmer.kinerja', [
        'assignee_id' => $programmerUser->id,
        'from' => $oneYearAgo,
        'to' => $todayString,
    ])),
];

// Create large dataset for edge case testing
DB::beginTransaction();
try {
    echo "Creating large dataset for edge case testing...\n";
    
    $bulkRunId = (string) str()->uuid();
    $bulkBugs = [];
    $bugCount = 800;
    $resolvedEvery = 3;

    $now = Carbon::now();

    for ($i = 1; $i <= $bugCount; $i++) {
        $status = ($i % $resolvedEvery === 0) ? 'Resolved' : (($i % 2 === 0) ? 'In Progress' : 'Assigned');
        $assigneeId = $status === 'Assigned' && ($i % 4 !== 0) ? null : $programmerUser->id;

        $createdAt = $now->copy()->subDays($i % 365)->subMinutes($i);
        $updatedAt = $createdAt->copy()->addHours(($i % 12) + 1);

        $bulkBugs[] = [
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $assigneeId,
            'guest_name' => 'Bulk Guest '.$i,
            'guest_email' => 'bulk-prog-'.$bulkRunId.'-'.$i.'@example.test',
            'guest_version' => '9.9.'.$i,
            'title' => 'Bulk Audit Bug '.$i,
            'description' => 'Bulk generated bug for edge test '.$i,
            'frequency' => 'Often',
            'status' => $status,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'deleted_at' => null,
        ];
    }

    foreach (array_chunk($bulkBugs, 300) as $chunk) {
        DB::table('bugs')->insert($chunk);
    }

    // Map sequence -> bug_id actual
    $insertedBugs = DB::table('bugs')
        ->where('guest_email', 'like', 'bulk-prog-'.$bulkRunId.'-%@example.test')
        ->select(['id', 'guest_email'])
        ->get();

    $sequenceToBugId = [];
    foreach ($insertedBugs as $row) {
        $guestEmail = (string) ($row->guest_email ?? '');
        if (preg_match('/bulk\-prog\-[a-f0-9\-]+\-(\d+)@example\.test$/', $guestEmail, $m)) {
            $sequenceToBugId[(int) $m[1]] = (int) $row->id;
        }
    }

    // Add status histories for resolved bugs
    $historyRows = [];
    $resolvedCount = 0;
    
    foreach ($bulkBugs as $idx => $bugData) {
        if ($bugData['status'] === 'Resolved') {
            $seq = $idx + 1;
            $bugId = $sequenceToBugId[$seq] ?? null;
            
            if ($bugId) {
                $historyRows[] = [
                    'bug_id' => $bugId,
                    'user_id' => $programmerUser->id,
                    'old_status' => 'Testing',
                    'new_status' => 'Resolved',
                    'changed_at' => $bugData['updated_at'],
                ];
                $resolvedCount++;
            }
        }
    }

    if (!empty($historyRows)) {
        foreach (array_chunk($historyRows, 300) as $chunk) {
            DB::table('bug_status_histories')->insert($chunk);
        }
    }

    // Add notifications
    $bulkNotifications = [];
    $notifCount = 400;

    $insertedBugIds = array_values($sequenceToBugId);
    $bugIdCount = count($insertedBugIds);

    if ($bugIdCount > 0) {
        for ($i = 1; $i <= $notifCount; $i++) {
            $relatedId = $insertedBugIds[($i - 1) % $bugIdCount];

            $bulkNotifications[] = [
                'user_id' => $programmerUser->id,
                'related_id' => $relatedId,
                'type' => 'BugAssigned',
                'message' => 'Bulk notif '.$i,
                'is_read' => false,
                'created_at' => $now->copy()->subMinutes($i),
            ];
        }

        foreach (array_chunk($bulkNotifications, 300) as $chunk) {
            DB::table('notifications')->insert($chunk);
        }
    }

    echo "Large dataset created: {$bugCount} bugs, ~{$resolvedCount} resolved, {$notifCount} notifications\n\n";

    // Run edge case tests with large dataset
    $edgeCase['large_dataset'] = [
        'dashboard_page_1' => runListMetric($kernel, $programmerUser, routePath('programmer.dashboard', ['page' => 1])),
        'dashboard_page_25' => runListMetric($kernel, $programmerUser, routePath('programmer.dashboard', ['page' => 25])),
        'dashboard_page_50' => runListMetric($kernel, $programmerUser, routePath('programmer.dashboard', ['page' => 50])),
        'notifications_page_1' => runListMetric($kernel, $programmerUser, routePath('programmer.notifications', ['page' => 1])),
        'notifications_page_10' => runListMetric($kernel, $programmerUser, routePath('programmer.notifications', ['page' => 10])),
        'notifications_page_20' => runListMetric($kernel, $programmerUser, routePath('programmer.notifications', ['page' => 20])),
        'kinerja_1_year' => runListMetric($kernel, $programmerUser, routePath('programmer.kinerja', [
            'assignee_id' => $programmerUser->id,
            'from' => $oneYearAgo,
            'to' => $todayString,
        ])),
    ];
} finally {
    safeRollback();
}

// Comparison
$pairs = [
    'dashboard_page_1' => ['small' => 'dashboard_page_1', 'large' => 'dashboard_page_1'],
    'dashboard_page_depth' => ['small' => 'dashboard_page_2', 'large' => 'dashboard_page_50'],
    'notifications_page_1' => ['small' => 'notifications_page_1', 'large' => 'notifications_page_1'],
    'notifications_page_depth' => ['small' => 'notifications_page_3', 'large' => 'notifications_page_20'],
    'kinerja_1_year' => ['small' => 'kinerja_1_year', 'large' => 'kinerja_1_year'],
];

foreach ($pairs as $label => $pair) {
    $small = $edgeCase['small_dataset'][$pair['small']] ?? null;
    $large = $edgeCase['large_dataset'][$pair['large']] ?? null;

    if (! $small || ! $large) {
        continue;
    }

    $smallTotal = (float) ($small['total_time_ms'] ?? 0.0);
    $largeTotal = (float) ($large['total_time_ms'] ?? 0.0);
    $smallMax = (float) ($small['max_query_time_ms'] ?? 0.0);
    $largeMax = (float) ($large['max_query_time_ms'] ?? 0.0);

    $edgeCase['comparison'][$label] = [
        'small' => $small,
        'large' => $large,
        'delta_total_time_ms' => round($largeTotal - $smallTotal, 3),
        'delta_max_query_ms' => round($largeMax - $smallMax, 3),
        'ratio_total_time' => $smallTotal > 0 ? round($largeTotal / $smallTotal, 3) : null,
    ];
}

// =============================================================================
// GENERATE SUMMARY
// =============================================================================

echo "\n=====================================================\n";
echo "  SUMMARY STATISTICS\n";
echo "=====================================================\n\n";

$totalQueries = array_sum(array_column($results, 'total_queries'));
$totalTime = array_sum(array_column($results, 'total_time_ms'));
$avgTime = count($results) > 0 ? $totalTime / count($results) : 0;
$maxTime = max(array_column($results, 'total_time_ms'));
$totalSlowQueries = array_sum(array_map(fn($r) => count($r['slow_queries'] ?? []), $results));
$totalDuplicates = array_sum(array_map(fn($r) => count($r['duplicates'] ?? []), $results));

echo "Total pages tested: ".count($results)."\n";
echo "Total queries executed: {$totalQueries}\n";
echo "Total execution time: ".round($totalTime, 2)."ms\n";
echo "Average time per page: ".round($avgTime, 2)."ms\n";
echo "Max time (slowest page): ".round($maxTime, 2)."ms\n";
echo "Total slow queries (>20ms): {$totalSlowQueries}\n";
echo "Total duplicate queries: {$totalDuplicates}\n";
echo "Unique slow queries: ".count($slowExplains)."\n";
echo "Tables used: ".count($usedTables)."\n\n";

// =============================================================================
// SAVE OUTPUT
// =============================================================================

$final = [
    'generated_at' => now()->toDateTimeString(),
    'role' => 'Programmer',
    'programmer_user' => [
        'id' => $programmerUser->id,
        'email' => $programmerUser->email,
    ],
    'route_count' => count($results),
    'summary' => [
        'total_queries' => $totalQueries,
        'total_time_ms' => round($totalTime, 2),
        'avg_time_ms' => round($avgTime, 2),
        'max_time_ms' => round($maxTime, 2),
        'slow_query_count' => $totalSlowQueries,
        'duplicate_count' => $totalDuplicates,
        'unique_slow_queries' => count($slowExplains),
    ],
    'results' => $results,
    'slow_query_explains' => $slowExplains,
    'table_indexes' => $tableIndexes,
    'edge_case' => $edgeCase,
];

$outputPath = storage_path('app/programmer_database_audit_report.json');
file_put_contents($outputPath, json_encode($final, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "=====================================================\n";
echo "  OUTPUT SAVED\n";
echo "=====================================================\n\n";
echo "Audit selesai. Output: {$outputPath}\n";
echo "Total scenario: ".count($results)."\n";
echo "Total slow unique query: ".count($slowExplains)."\n";

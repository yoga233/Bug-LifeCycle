<?php

declare(strict_types=1);

/**
 * QA DATABASE AUDIT SCRIPT
 * 
 * Performs comprehensive database query audit for all pages accessible by QA role.
 * 
 * Usage: php scripts/qa_database_audit.php
 * 
 * Target role: QA
 * Routes tested: qa.testing-queue, qa.notifications, qa.bugs.show, 
 *                qa.bugs.approve, qa.bugs.reject, 
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
function auditScenario(string $name, callable $builder, HttpKernel $kernel, User $qaUser): array
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
        Auth::guard('web')->setUser($qaUser);

        $server = [
            'HTTP_HOST' => 'localhost',
            'HTTPS' => 'off',
        ];

        $cookies = [
            $session->getName() => $session->getId(),
        ];

        $request = Request::create($uri, $method, $payload, $cookies, [], $server);
        $request->setLaravelSession($session);
        $request->setUserResolver(fn () => $qaUser);

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

function runListMetric(HttpKernel $kernel, User $qaUser, string $uri): array
{
    $result = auditScenario(
        name: 'list_metric '.$uri,
        builder: static fn () => ['method' => 'GET', 'uri' => $uri],
        kernel: $kernel,
        qaUser: $qaUser,
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
echo "  QA DATABASE AUDIT SCRIPT\n";
echo "  Role: QA\n";
echo "  Target: 15-20+ pages/scenarios\n";
echo "=====================================================\n\n";

// Find QA user
/** @var User|null $qaUser */
$qaUser = User::query()
    ->where('is_active', true)
    ->whereHas('roles', fn ($q) => $q->where('name', 'QA'))
    ->first();

if (! $qaUser) {
    fwrite(STDERR, "Tidak ditemukan user QA aktif. Jalankan seeder terlebih dahulu.\n");
    exit(1);
}

echo "Found QA user: {$qaUser->email} (ID: {$qaUser->id})\n\n";

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
// QA SPECIFIC SCENARIOS (15-20 pages)
// =============================================================================

// 1. QA Testing Queue Index
$scenarios[] = [
    'name' => 'QA Testing Queue Index',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('qa.testing-queue'),
    ],
];

// 2. QA Testing Queue with filters
$scenarios[] = [
    'name' => 'QA Testing Queue (Filtered)',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('qa.testing-queue', [
            'status' => 'Testing',
            'priority' => 'High',
        ]),
    ],
];

// 3. QA Testing Queue - empty results
$scenarios[] = [
    'name' => 'QA Testing Queue (Empty Filter)',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('qa.testing-queue', [
            'status' => 'Resolved',
        ]),
    ],
];

// 4. QA Notifications Index
$scenarios[] = [
    'name' => 'QA Notifications Index',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('qa.notifications'),
    ],
];

// 5. QA Bug Show (bug in Testing status)
$scenarios[] = [
    'name' => 'QA Bug Show',
    'builder' => static function () use ($project, $severity, $priority, $qaUser): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $qaUser->id,
            'guest_name' => 'Audit Guest QA',
            'guest_email' => 'audit-show-qa@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Audit show bug QA',
            'description' => 'Audit detail bug for QA',
            'frequency' => 'Often',
            'status' => 'Testing',
        ]);

        return [
            'method' => 'GET',
            'uri' => routePath('qa.bugs.show', ['bug' => $bug->id]),
        ];
    },
];

// 6. QA Bug Show with comments
$scenarios[] = [
    'name' => 'QA Bug Show with Comments',
    'builder' => static function () use ($project, $severity, $priority, $qaUser): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $qaUser->id,
            'guest_name' => 'Audit Guest Comments QA',
            'guest_email' => 'audit-comments-qa@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Bug with comments QA',
            'description' => 'Bug for comment test',
            'frequency' => 'Often',
            'status' => 'Testing',
        ]);

        // Add some comments
        Comment::query()->insert([
            ['bug_id' => $bug->id, 'user_id' => $qaUser->id, 'content' => 'Comment 1 QA', 'created_at' => now()],
            ['bug_id' => $bug->id, 'user_id' => $qaUser->id, 'content' => 'Comment 2 QA', 'created_at' => now()],
        ]);

        return [
            'method' => 'GET',
            'uri' => routePath('qa.bugs.show', ['bug' => $bug->id]),
        ];
    },
];

// 7. QA Bug Show with attachments
$scenarios[] = [
    'name' => 'QA Bug Show with Attachments',
    'builder' => static function () use ($project, $severity, $priority, $qaUser): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $qaUser->id,
            'guest_name' => 'Attachment Guest QA',
            'guest_email' => 'attachments-qa@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Bug with attachments QA',
            'description' => 'Bug for attachment test',
            'frequency' => 'Often',
            'status' => 'Testing',
        ]);

        // Add attachments (using Attachment model)
        $attachmentClass = 'App\\Models\\Attachment';
        if (class_exists($attachmentClass)) {
            $attachmentClass::query()->insert([
                ['bug_id' => $bug->id, 'file_name' => 'screenshot1.png', 'file_path' => '/uploads/test1.png', 'file_size' => 1024, 'file_type' => 'image/png', 'created_at' => now()],
                ['bug_id' => $bug->id, 'file_name' => 'screenshot2.jpg', 'file_path' => '/uploads/test2.jpg', 'file_size' => 2048, 'file_type' => 'image/jpeg', 'created_at' => now()],
            ]);
        }

        return [
            'method' => 'GET',
            'uri' => routePath('qa.bugs.show', ['bug' => $bug->id]),
        ];
    },
];

// 8. QA Bug Show with status history
$scenarios[] = [
    'name' => 'QA Bug Show with Status History',
    'builder' => static function () use ($project, $severity, $priority, $qaUser): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $qaUser->id,
            'guest_name' => 'History Guest QA',
            'guest_email' => 'history-qa@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Bug with history QA',
            'description' => 'Bug for history test',
            'frequency' => 'Often',
            'status' => 'Testing',
        ]);

        // Add status history
        BugStatusHistory::query()->insert([
            ['bug_id' => $bug->id, 'user_id' => $qaUser->id, 'old_status' => 'Reported', 'new_status' => 'Assigned', 'changed_at' => now()->subDays(3)],
            ['bug_id' => $bug->id, 'user_id' => $qaUser->id, 'old_status' => 'Assigned', 'new_status' => 'In Progress', 'changed_at' => now()->subDays(2)],
            ['bug_id' => $bug->id, 'user_id' => $qaUser->id, 'old_status' => 'In Progress', 'new_status' => 'Testing', 'changed_at' => now()->subDay()],
        ]);

        return [
            'method' => 'GET',
            'uri' => routePath('qa.bugs.show', ['bug' => $bug->id]),
        ];
    },
];

// 9. QA Bug Approve (resolve the bug)
$scenarios[] = [
    'name' => 'QA Bug Approve',
    'builder' => static function () use ($project, $severity, $priority, $qaUser): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $qaUser->id,
            'guest_name' => 'Approve Guest QA',
            'guest_email' => 'approve-qa@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Bug to approve',
            'description' => 'Bug ready for approval',
            'frequency' => 'Often',
            'status' => 'Testing',
        ]);

        return [
            'method' => 'POST',
            'uri' => routePath('qa.bugs.approve', ['bug' => $bug->id]),
        ];
    },
];

// 10. QA Bug Reject (send back to programmer)
$scenarios[] = [
    'name' => 'QA Bug Reject',
    'builder' => static function () use ($project, $severity, $priority, $qaUser): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $qaUser->id,
            'guest_name' => 'Reject Guest QA',
            'guest_email' => 'reject-qa@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Bug to reject',
            'description' => 'Bug to reject and send back',
            'frequency' => 'Often',
            'status' => 'Testing',
        ]);

        return [
            'method' => 'POST',
            'uri' => routePath('qa.bugs.reject', ['bug' => $bug->id]),
            'payload' => ['reason' => 'Bug masih terjadi, perlu perbaikan.'],
        ];
    },
];

// 11. QA Notifications Mark All Read
$scenarios[] = [
    'name' => 'QA Notifications Mark All Read',
    'builder' => static function () use ($qaUser, $project, $severity, $priority): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $qaUser->id,
            'guest_name' => 'Notif All Read Guest QA',
            'guest_email' => 'notif-all-read-qa@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Notif all read bug QA',
            'description' => 'Notif all read bug',
            'frequency' => 'Often',
            'status' => 'Testing',
        ]);

        Notification::query()->insert([
            [
                'user_id' => $qaUser->id,
                'related_id' => $bug->id,
                'type' => 'BugAssigned',
                'message' => 'Notif unread 1 QA',
                'is_read' => false,
                'created_at' => now(),
            ],
            [
                'user_id' => $qaUser->id,
                'related_id' => $bug->id,
                'type' => 'BugStatusChanged',
                'message' => 'Notif unread 2 QA',
                'is_read' => false,
                'created_at' => now(),
            ],
        ]);

        return [
            'method' => 'POST',
            'uri' => routePath('qa.notifications.markAllRead'),
        ];
    },
];

// 12. QA Notifications Mark Single Read
$scenarios[] = [
    'name' => 'QA Notifications Mark Read',
    'builder' => static function () use ($qaUser, $project, $severity, $priority): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $qaUser->id,
            'guest_name' => 'Notif Read Guest QA',
            'guest_email' => 'notif-read-qa@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Notif read bug QA',
            'description' => 'Notif read bug',
            'frequency' => 'Often',
            'status' => 'Testing',
        ]);

        $notification = Notification::query()->create([
            'user_id' => $qaUser->id,
            'related_id' => $bug->id,
            'type' => 'BugAssigned',
            'message' => 'Notif mark read QA',
            'is_read' => false,
            'created_at' => now(),
        ]);

        return [
            'method' => 'POST',
            'uri' => routePath('qa.notifications.read', ['notification' => $notification->id]),
        ];
    },
];

// 13. QA Notifications Destroy
$scenarios[] = [
    'name' => 'QA Notifications Destroy',
    'builder' => static function () use ($qaUser, $project, $severity, $priority): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $qaUser->id,
            'guest_name' => 'Notif Destroy Guest QA',
            'guest_email' => 'notif-destroy-qa@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Notif destroy bug QA',
            'description' => 'Notif destroy bug',
            'frequency' => 'Often',
            'status' => 'Testing',
        ]);

        $notification = Notification::query()->create([
            'user_id' => $qaUser->id,
            'related_id' => $bug->id,
            'type' => 'BugAssigned',
            'message' => 'Notif destroy QA',
            'is_read' => false,
            'created_at' => now(),
        ]);

        return [
            'method' => 'DELETE',
            'uri' => routePath('qa.notifications.destroy', ['notification' => $notification->id]),
        ];
    },
];

// 14. Profile Edit (shared by all roles)
$scenarios[] = [
    'name' => 'Profile Edit',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('profile.edit'),
    ],
];

// 15. Profile Update
$scenarios[] = [
    'name' => 'Profile Update',
    'builder' => static function (): array {
        return [
            'method' => 'PATCH',
            'uri' => routePath('profile.update'),
            'payload' => [
                'name' => 'Updated QA Name',
                'email' => 'updated-qa@example.test',
            ],
        ];
    },
];

// 16. Dashboard Redirect (entry point)
$scenarios[] = [
    'name' => 'Dashboard Redirect (to qa.testing-queue)',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('dashboard'),
    ],
];

// 17. QA Testing Queue with pagination page 2
$scenarios[] = [
    'name' => 'QA Testing Queue Page 2',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('qa.testing-queue', ['page' => 2]),
    ],
];

// 18. QA Testing Queue with pagination page 5
$scenarios[] = [
    'name' => 'QA Testing Queue Page 5',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('qa.testing-queue', ['page' => 5]),
    ],
];

// 19. QA Notifications with pagination page 2
$scenarios[] = [
    'name' => 'QA Notifications Page 2',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('qa.notifications', ['page' => 2]),
    ],
];

// 20. QA Bug Show - different status (In Progress)
$scenarios[] = [
    'name' => 'QA Bug Show (In Progress Status)',
    'builder' => static function () use ($project, $severity, $priority, $qaUser): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $qaUser->id,
            'guest_name' => 'In Progress Guest QA',
            'guest_email' => 'inprogress-qa@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Bug in progress QA',
            'description' => 'Bug still in progress',
            'frequency' => 'Often',
            'status' => 'In Progress',
        ]);

        return [
            'method' => 'GET',
            'uri' => routePath('qa.bugs.show', ['bug' => $bug->id]),
        ];
    },
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
        qaUser: $qaUser,
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
    'testing_queue_page_1' => runListMetric($kernel, $qaUser, routePath('qa.testing-queue', ['page' => 1])),
    'testing_queue_page_2' => runListMetric($kernel, $qaUser, routePath('qa.testing-queue', ['page' => 2])),
    'notifications_page_1' => runListMetric($kernel, $qaUser, routePath('qa.notifications', ['page' => 1])),
    'notifications_page_3' => runListMetric($kernel, $qaUser, routePath('qa.notifications', ['page' => 3])),
];

// Create large dataset for edge case testing
DB::beginTransaction();
try {
    echo "Creating large dataset for edge case testing...\n";
    
    $bulkRunId = (string) str()->uuid();
    $bulkBugs = [];
    $bugCount = 800;
    $testingEvery = 5; // Every 5th bug will be in Testing status for QA

    $now = Carbon::now();

    for ($i = 1; $i <= $bugCount; $i++) {
        // QA primarily deals with bugs in Testing status
        $status = ($i % $testingEvery === 0) ? 'Testing' : (($i % 3 === 0) ? 'Resolved' : 'In Progress');
        $assigneeId = $status === 'Testing' ? $qaUser->id : (($i % 2 === 0) ? null : $qaUser->id);

        $createdAt = $now->copy()->subDays($i % 365)->subMinutes($i);
        $updatedAt = $createdAt->copy()->addHours(($i % 12) + 1);

        $bulkBugs[] = [
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => $priority->id,
            'assignee_id' => $assigneeId,
            'guest_name' => 'Bulk Guest QA '.$i,
            'guest_email' => 'bulk-qa-'.$bulkRunId.'-'.$i.'@example.test',
            'guest_version' => '9.9.'.$i,
            'title' => 'Bulk Audit Bug QA '.$i,
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
        ->where('guest_email', 'like', 'bulk-qa-'.$bulkRunId.'-%@example.test')
        ->select(['id', 'guest_email'])
        ->get();

    $sequenceToBugId = [];
    foreach ($insertedBugs as $row) {
        $guestEmail = (string) ($row->guest_email ?? '');
        if (preg_match('/bulk\-qa\-[a-f0-9\-]+\-(\d+)@example\.test$/', $guestEmail, $m)) {
            $sequenceToBugId[(int) $m[1]] = (int) $row->id;
        }
    }

    // Add status histories for Testing and Resolved bugs
    $historyRows = [];
    $testingCount = 0;
    
    foreach ($bulkBugs as $idx => $bugData) {
        if (in_array($bugData['status'], ['Testing', 'Resolved'])) {
            $seq = $idx + 1;
            $bugId = $sequenceToBugId[$seq] ?? null;
            
            if ($bugId) {
                $historyRows[] = [
                    'bug_id' => $bugId,
                    'user_id' => $qaUser->id,
                    'old_status' => 'In Progress',
                    'new_status' => $bugData['status'],
                    'changed_at' => $bugData['updated_at'],
                ];
                if ($bugData['status'] === 'Testing') {
                    $testingCount++;
                }
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
                'user_id' => $qaUser->id,
                'related_id' => $relatedId,
                'type' => 'BugStatusChanged',
                'message' => 'Bulk notif QA '.$i,
                'is_read' => false,
                'created_at' => $now->copy()->subMinutes($i),
            ];
        }

        foreach (array_chunk($bulkNotifications, 300) as $chunk) {
            DB::table('notifications')->insert($chunk);
        }
    }

    echo "Large dataset created: {$bugCount} bugs, ~{$testingCount} in Testing, {$notifCount} notifications\n\n";

    // Run edge case tests with large dataset
    $edgeCase['large_dataset'] = [
        'testing_queue_page_1' => runListMetric($kernel, $qaUser, routePath('qa.testing-queue', ['page' => 1])),
        'testing_queue_page_25' => runListMetric($kernel, $qaUser, routePath('qa.testing-queue', ['page' => 25])),
        'testing_queue_page_50' => runListMetric($kernel, $qaUser, routePath('qa.testing-queue', ['page' => 50])),
        'notifications_page_1' => runListMetric($kernel, $qaUser, routePath('qa.notifications', ['page' => 1])),
        'notifications_page_10' => runListMetric($kernel, $qaUser, routePath('qa.notifications', ['page' => 10])),
        'notifications_page_20' => runListMetric($kernel, $qaUser, routePath('qa.notifications', ['page' => 20])),
    ];
} finally {
    safeRollback();
}

// Comparison
$pairs = [
    'testing_queue_page_1' => ['small' => 'testing_queue_page_1', 'large' => 'testing_queue_page_1'],
    'testing_queue_page_depth' => ['small' => 'testing_queue_page_2', 'large' => 'testing_queue_page_50'],
    'notifications_page_1' => ['small' => 'notifications_page_1', 'large' => 'notifications_page_1'],
    'notifications_page_depth' => ['small' => 'notifications_page_3', 'large' => 'notifications_page_20'],
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
    'role' => 'QA',
    'qa_user' => [
        'id' => $qaUser->id,
        'email' => $qaUser->email,
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

$outputPath = storage_path('app/qa_database_audit_report.json');
file_put_contents($outputPath, json_encode($final, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "=====================================================\n";
echo "  OUTPUT SAVED\n";
echo "=====================================================\n\n";
echo "Audit selesai. Output: {$outputPath}\n";
echo "Total scenario: ".count($results)."\n";
echo "Total slow unique query: ".count($slowExplains)."\n";

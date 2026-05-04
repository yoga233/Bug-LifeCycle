<?php

declare(strict_types=1);

/**
 * CLIENT DATABASE AUDIT SCRIPT
 * 
 * Performs comprehensive database query audit for all pages accessible by Client role.
 * 
 * Usage: php scripts/client_database_audit.php
 * 
 * Target role: Client (portal publik - tanpa login)
 * Routes tested: client.landing, client.report, client.report.store,
 *                 client.report.success, client.tracking
 */

use App\Models\Attachment;
use App\Models\Bug;
use App\Models\BugStatusHistory;
use App\Models\Notification;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Severity;
use App\Models\User;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
function auditScenario(string $name, callable $builder, HttpKernel $kernel): array
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

        // Client portal doesn't require auth - guest access
        $server = [
            'HTTP_HOST' => 'localhost',
            'HTTPS' => 'off',
        ];

        $cookies = [];

        $request = Request::create($uri, $method, $payload, $cookies, [], $server);

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

function runListMetric(HttpKernel $kernel, string $uri): array
{
    $result = auditScenario(
        name: 'list_metric '.$uri,
        builder: static fn () => ['method' => 'GET', 'uri' => $uri],
        kernel: $kernel,
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
echo "  CLIENT DATABASE AUDIT SCRIPT\n";
echo "  Role: Client (Portal Publik)\n";
echo "  Target: 15-20+ pages/scenarios\n";
echo "=====================================================\n\n";

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

echo "Reference data ready (project: {$project->id}, severity: {$severity->id}, priority: {$priority->id})\n\n";

$scenarios = [];

// =============================================================================
// CLIENT SPECIFIC SCENARIOS (15-20 pages)
// =============================================================================

// 1. Client Landing Page (portal home)
$scenarios[] = [
    'name' => 'Client Landing Page',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('client.landing'),
    ],
];

// 2. Client Bug Report Form (create)
$scenarios[] = [
    'name' => 'Client Bug Report Form',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('client.report'),
    ],
];

// 3. Client Bug Report - with multiple projects
$scenarios[] = [
    'name' => 'Client Bug Report Form (Multiple Projects)',
    'builder' => static function (): array {
        // Create additional projects
        for ($i = 1; $i <= 5; $i++) {
            Project::query()->firstOrCreate(
                ['name' => "Audit Project {$i}"],
                ['platform' => 'Web', 'description' => "Test project {$i}"]
            );
        }

        return [
            'method' => 'GET',
            'uri' => routePath('client.report'),
        ];
    },
];

// 4. Client Bug Report Store - basic submission
$scenarios[] = [
    'name' => 'Client Bug Report Store (Basic)',
    'builder' => static function () use ($project, $severity): array {
        return [
            'method' => 'POST',
            'uri' => routePath('client.report.store'),
            'payload' => [
                'guest_name' => 'Audit Guest',
                'guest_email' => 'audit-basic@example.test',
                'guest_version' => '1.0.0',
                'project_id' => $project->id,
                'severity_id' => $severity->id,
                'title' => 'Audit Bug Basic',
                'description' => 'Basic audit bug description',
                'reproduction_steps' => 'Step 1: Do something',
                'frequency' => 'once',
            ],
        ];
    },
];

// 5. Client Bug Report Store - full submission with attachments (simulated)
$scenarios[] = [
    'name' => 'Client Bug Report Store (Full)',
    'builder' => static function () use ($project, $severity): array {
        return [
            'method' => 'POST',
            'uri' => routePath('client.report.store'),
            'payload' => [
                'guest_name' => 'Audit Full Guest',
                'guest_email' => 'audit-full@example.test',
                'guest_version' => '2.0.0',
                'project_id' => $project->id,
                'severity_id' => $severity->id,
                'title' => 'Audit Bug Full Submission',
                'description' => 'Full audit bug description with all fields',
                'reproduction_steps' => "1. Open app\n2. Click submit\n3. See error",
                'frequency' => 'frequent',
            ],
        ];
    },
];

// 6. Client Report Success Page
$scenarios[] = [
    'name' => 'Client Report Success Page',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('client.report.success', ['ticket' => 'BUG-TEST1']),
    ],
];

// 7. Client Bug Tracking - initial load (no ticket)
$scenarios[] = [
    'name' => 'Client Bug Tracking (No Ticket)',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('client.tracking'),
    ],
];

// 8. Client Bug Tracking - with valid ticket
$scenarios[] = [
    'name' => 'Client Bug Tracking (With Ticket)',
    'builder' => static function () use ($project, $severity): array {
        // Create a test bug to track
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => null,
            'assignee_id' => null,
            'guest_name' => 'Track Guest',
            'guest_email' => 'track@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Trackable Bug',
            'description' => 'Bug for tracking test',
            'frequency' => 'rare',
            'status' => 'Reported',
        ]);

        $ticketService = app(\App\Services\TicketService::class);
        $ticket = $ticketService->fromBugId($bug->id);

        return [
            'method' => 'GET',
            'uri' => routePath('client.tracking', ['ticket' => $ticket]),
        ];
    },
];

// 9. Client Bug Tracking - with valid ticket and status history
$scenarios[] = [
    'name' => 'Client Bug Tracking (With History)',
    'builder' => static function () use ($project, $severity): array {
        // Create a bug with status history
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => null,
            'assignee_id' => null,
            'guest_name' => 'History Guest',
            'guest_email' => 'history@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Bug With History',
            'description' => 'Bug with multiple status changes',
            'frequency' => 'once',
            'status' => 'In Progress',
        ]);

        // Add status history
        BugStatusHistory::query()->insert([
            ['bug_id' => $bug->id, 'user_id' => null, 'old_status' => 'Reported', 'new_status' => 'Reported', 'changed_at' => now()->subDays(2)],
            ['bug_id' => $bug->id, 'user_id' => null, 'old_status' => 'Reported', 'new_status' => 'Assigned', 'changed_at' => now()->subDay()],
            ['bug_id' => $bug->id, 'user_id' => null, 'old_status' => 'Assigned', 'new_status' => 'In Progress', 'changed_at' => now()],
        ]);

        $ticketService = app(\App\Services\TicketService::class);
        $ticket = $ticketService->fromBugId($bug->id);

        return [
            'method' => 'GET',
            'uri' => routePath('client.tracking', ['ticket' => $ticket]),
        ];
    },
];

// 10. Client Bug Tracking - invalid ticket format
$scenarios[] = [
    'name' => 'Client Bug Tracking (Invalid Format)',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('client.tracking', ['ticket' => 'INVALID-TICKET']),
    ],
];

// 11. Client Bug Tracking - not found ticket
$scenarios[] = [
    'name' => 'Client Bug Tracking (Not Found)',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('client.tracking', ['ticket' => 'BUG-99999']),
    ],
];

// 12. Client Report Form - validation error test (empty)
$scenarios[] = [
    'name' => 'Client Bug Report Store (Validation Error)',
    'builder' => static fn () => [
        'method' => 'POST',
        'uri' => routePath('client.report.store'),
        'payload' => [
            'guest_name' => '',
            'guest_email' => 'not-an-email',
            'guest_version' => '',
            'project_id' => '',
            'severity_id' => '',
            'title' => '',
            'description' => '',
            'reproduction_steps' => '',
            'frequency' => '',
        ],
    ],
];

// 13. Client Report Form - large description
$scenarios[] = [
    'name' => 'Client Bug Report Store (Large Description)',
    'builder' => static function () use ($project, $severity): array {
        $largeDescription = str_repeat("This is a test description. ", 100);
        $largeRepro = str_repeat("Step X: Do something. ", 50);

        return [
            'method' => 'POST',
            'uri' => routePath('client.report.store'),
            'payload' => [
                'guest_name' => 'Large Desc Guest',
                'guest_email' => 'large-desc@example.test',
                'guest_version' => '1.0.0',
                'project_id' => $project->id,
                'severity_id' => $severity->id,
                'title' => 'Bug with large description',
                'description' => $largeDescription,
                'reproduction_steps' => $largeRepro,
                'frequency' => 'frequent',
            ],
        ];
    },
];

// 14. Client Tracking - with resolved bug
$scenarios[] = [
    'name' => 'Client Bug Tracking (Resolved Bug)',
    'builder' => static function () use ($project, $severity): array {
        // Create a resolved bug
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => null,
            'assignee_id' => null,
            'guest_name' => 'Resolved Guest',
            'guest_email' => 'resolved@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Resolved Bug',
            'description' => 'This bug has been resolved',
            'frequency' => 'once',
            'status' => 'Resolved',
        ]);

        // Add status history showing resolution
        BugStatusHistory::query()->insert([
            ['bug_id' => $bug->id, 'user_id' => null, 'old_status' => 'Reported', 'new_status' => 'Reported', 'changed_at' => now()->subDays(5)],
            ['bug_id' => $bug->id, 'user_id' => null, 'old_status' => 'Reported', 'new_status' => 'Assigned', 'changed_at' => now()->subDays(4)],
            ['bug_id' => $bug->id, 'user_id' => null, 'old_status' => 'Assigned', 'new_status' => 'In Progress', 'changed_at' => now()->subDays(3)],
            ['bug_id' => $bug->id, 'user_id' => null, 'old_status' => 'In Progress', 'new_status' => 'Testing', 'changed_at' => now()->subDays(2)],
            ['bug_id' => $bug->id, 'user_id' => null, 'old_status' => 'Testing', 'new_status' => 'Resolved', 'changed_at' => now()->subDay()],
        ]);

        $ticketService = app(\App\Services\TicketService::class);
        $ticket = $ticketService->fromBugId($bug->id);

        return [
            'method' => 'GET',
            'uri' => routePath('client.tracking', ['ticket' => $ticket]),
        ];
    },
];

// 15. Client Tracking - with rejected bug
$scenarios[] = [
    'name' => 'Client Bug Tracking (Rejected Bug)',
    'builder' => static function () use ($project, $severity): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => null,
            'assignee_id' => null,
            'guest_name' => 'Rejected Guest',
            'guest_email' => 'rejected@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Rejected Bug',
            'description' => 'This bug was rejected',
            'frequency' => 'once',
            'status' => 'Rejected',
        ]);

        BugStatusHistory::query()->insert([
            ['bug_id' => $bug->id, 'user_id' => null, 'old_status' => 'Reported', 'new_status' => 'Reported', 'changed_at' => now()->subDays(2)],
            ['bug_id' => $bug->id, 'user_id' => null, 'old_status' => 'Reported', 'new_status' => 'Rejected', 'changed_at' => now()->subDay()],
        ]);

        $ticketService = app(\App\Services\TicketService::class);
        $ticket = $ticketService->fromBugId($bug->id);

        return [
            'method' => 'GET',
            'uri' => routePath('client.tracking', ['ticket' => $ticket]),
        ];
    },
];

// 16. Client Bug Tracking - with attachments
$scenarios[] = [
    'name' => 'Client Bug Tracking (With Attachments)',
    'builder' => static function () use ($project, $severity): array {
        $bug = Bug::query()->create([
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => null,
            'assignee_id' => null,
            'guest_name' => 'Attachment Guest',
            'guest_email' => 'attachment@example.test',
            'guest_version' => '1.0.0',
            'title' => 'Bug With Attachments',
            'description' => 'Bug that has attachments',
            'frequency' => 'once',
            'status' => 'In Progress',
        ]);

        // Add attachments
        Attachment::query()->insert([
            ['bug_id' => $bug->id, 'uploaded_by' => null, 'file_path' => '/uploads/test1.png', 'file_name' => 'screenshot.png', 'file_type' => 'image/png', 'file_size' => 1024, 'created_at' => now()],
            ['bug_id' => $bug->id, 'uploaded_by' => null, 'file_path' => '/uploads/test2.pdf', 'file_name' => 'log.pdf', 'file_type' => 'application/pdf', 'file_size' => 2048, 'created_at' => now()],
        ]);

        $ticketService = app(\App\Services\TicketService::class);
        $ticket = $ticketService->fromBugId($bug->id);

        return [
            'method' => 'GET',
            'uri' => routePath('client.tracking', ['ticket' => $ticket]),
        ];
    },
];

// 17. Client Report - different frequency options
$scenarios[] = [
    'name' => 'Client Bug Report Store (All Frequencies)',
    'builder' => static function () use ($project, $severity): array {
        $frequencies = ['once', 'rare', 'frequent', 'random'];
        $idx = array_rand($frequencies);

        return [
            'method' => 'POST',
            'uri' => routePath('client.report.store'),
            'payload' => [
                'guest_name' => 'Freq Guest',
                'guest_email' => 'freq-'.$idx.'@example.test',
                'guest_version' => '1.0.0',
                'project_id' => $project->id,
                'severity_id' => $severity->id,
                'title' => 'Bug with frequency: '.$frequencies[$idx],
                'description' => 'Testing frequency: '.$frequencies[$idx],
                'reproduction_steps' => 'Test',
                'frequency' => $frequencies[$idx],
            ],
        ];
    },
];

// 18. Client Report - with all severity levels
$scenarios[] = [
    'name' => 'Client Bug Report Store (Critical Severity)',
    'builder' => static function () use ($project, $severity): array {
        // Get critical severity
        $criticalSeverity = Severity::query()->first();

        return [
            'method' => 'POST',
            'uri' => routePath('client.report.store'),
            'payload' => [
                'guest_name' => 'Critical Guest',
                'guest_email' => 'critical@example.test',
                'guest_version' => '1.0.0',
                'project_id' => $project->id,
                'severity_id' => $criticalSeverity?->id ?? $severity->id,
                'title' => 'Critical Bug Report',
                'description' => 'This is a critical bug',
                'reproduction_steps' => 'Immediate reproduction',
                'frequency' => 'frequent',
            ],
        ];
    },
];

// 19. Client Tracking - sequential tracking requests
$scenarios[] = [
    'name' => 'Client Bug Tracking (Sequential Test)',
    'builder' => static function () use ($project, $severity): array {
        // Create multiple bugs for sequential tracking
        $bugs = [];
        for ($i = 1; $i <= 3; $i++) {
            $bug = Bug::query()->create([
                'project_id' => $project->id,
                'severity_id' => $severity->id,
                'priority_id' => null,
                'guest_name' => "Sequential Guest {$i}",
                'guest_email' => "seq{$i}@example.test",
                'guest_version' => '1.0.0',
                'title' => "Sequential Bug {$i}",
                'description' => "Bug number {$i}",
                'frequency' => 'once',
                'status' => 'Reported',
            ]);
            $bugs[] = $bug;
        }

        $ticketService = app(\App\Services\TicketService::class);
        $ticket = $ticketService->fromBugId($bugs[1]->id);

        return [
            'method' => 'GET',
            'uri' => routePath('client.tracking', ['ticket' => $ticket]),
        ];
    },
];

// 20. Client Landing - refresh (cache test)
$scenarios[] = [
    'name' => 'Client Landing Page (Refresh)',
    'builder' => static fn () => [
        'method' => 'GET',
        'uri' => routePath('client.landing'),
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

// Small dataset tests - tracking page
$edgeCase['small_dataset'] = [
    'tracking_no_ticket' => runListMetric($kernel, routePath('client.tracking')),
    'tracking_empty_ticket' => runListMetric($kernel, routePath('client.tracking', ['ticket' => ''])),
    'landing_page' => runListMetric($kernel, routePath('client.landing')),
    'report_form' => runListMetric($kernel, routePath('client.report')),
];

// Create large dataset for edge case testing
DB::beginTransaction();
try {
    echo "Creating large dataset for edge case testing...\n";
    
    $bulkRunId = (string) str()->uuid();
    $bulkBugs = [];
    $bugCount = 500;
    $resolvedEvery = 4;

    $now = Carbon::now();

    for ($i = 1; $i <= $bugCount; $i++) {
        $status = ($i % $resolvedEvery === 0) ? 'Resolved' : (($i % 3 === 0) ? 'In Progress' : 'Reported');
        
        $createdAt = $now->copy()->subDays($i % 365)->subMinutes($i);
        $updatedAt = $createdAt->copy()->addHours(($i % 12) + 1);

        $bulkBugs[] = [
            'project_id' => $project->id,
            'severity_id' => $severity->id,
            'priority_id' => null,
            'assignee_id' => null,
            'guest_name' => 'Bulk Guest '.$i,
            'guest_email' => 'bulk-'.$bulkRunId.'-'.$i.'@example.test',
            'guest_version' => '9.9.'.$i,
            'title' => 'Bulk Audit Bug '.$i,
            'description' => 'Bulk generated bug for edge test '.$i,
            'frequency' => 'once',
            'status' => $status,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'deleted_at' => null,
        ];
    }

    foreach (array_chunk($bulkBugs, 250) as $chunk) {
        DB::table('bugs')->insert($chunk);
    }

    // Map sequence -> bug_id actual
    $insertedBugs = DB::table('bugs')
        ->where('guest_email', 'like', 'bulk-'.$bulkRunId.'-%@example.test')
        ->select(['id', 'guest_email'])
        ->get();

    $sequenceToBugId = [];
    foreach ($insertedBugs as $row) {
        $guestEmail = (string) ($row->guest_email ?? '');
        if (preg_match('/bulk\-[a-f0-9\-]+\-(\d+)@example\.test$/', $guestEmail, $m)) {
            $sequenceToBugId[(int) $m[1]] = (int) $row->id;
        }
    }

    // Add status histories
    $historyRows = [];
    $resolvedCount = 0;
    
    foreach ($bulkBugs as $idx => $bugData) {
        if ($bugData['status'] === 'Resolved' || $bugData['status'] === 'In Progress') {
            $seq = $idx + 1;
            $bugId = $sequenceToBugId[$seq] ?? null;
            
            if ($bugId) {
                $historyRows[] = [
                    'bug_id' => $bugId,
                    'user_id' => null,
                    'old_status' => 'Reported',
                    'new_status' => $bugData['status'],
                    'changed_at' => $bugData['updated_at'],
                ];
                if ($bugData['status'] === 'Resolved') {
                    $resolvedCount++;
                }
            }
        }
    }

    if (!empty($historyRows)) {
        foreach (array_chunk($historyRows, 250) as $chunk) {
            DB::table('bug_status_histories')->insert($chunk);
        }
    }

    // Add attachments to some bugs
    $attachmentRows = [];
    $attCount = 100;
    
    $insertedBugIds = array_values($sequenceToBugId);
    $bugIdCount = count($insertedBugIds);

    if ($bugIdCount > 0) {
        for ($i = 1; $i <= $attCount; $i++) {
            $relatedId = $insertedBugIds[($i - 1) % $bugIdCount];

            $attachmentRows[] = [
                'bug_id' => $relatedId,
                'uploaded_by' => null,
                'file_path' => '/uploads/bulk-test-'.$i.'.png',
                'file_name' => 'test-'.$i.'.png',
                'file_type' => 'image/png',
                'file_size' => 1024,
                'created_at' => $now->copy()->subMinutes($i),
            ];
        }

        foreach (array_chunk($attachmentRows, 100) as $chunk) {
            DB::table('attachments')->insert($chunk);
        }
    }

    echo "Large dataset created: {$bugCount} bugs, ~{$resolvedCount} resolved, {$attCount} attachments\n\n";

    // Get a ticket from the large dataset
    $ticketService = app(\App\Services\TicketService::class);
    $sampleBugId = $insertedBugIds[0] ?? null;
    $sampleTicket = $sampleBugId ? $ticketService->fromBugId($sampleBugId) : 'BUG-TEST';

    // Run edge case tests with large dataset
    $edgeCase['large_dataset'] = [
        'tracking_with_ticket' => runListMetric($kernel, routePath('client.tracking', ['ticket' => $sampleTicket])),
        'tracking_not_found' => runListMetric($kernel, routePath('client.tracking', ['ticket' => 'BUG-999999'])),
        'landing_page' => runListMetric($kernel, routePath('client.landing')),
        'report_form' => runListMetric($kernel, routePath('client.report')),
    ];
} finally {
    safeRollback();
}

// Comparison
$pairs = [
    'landing_page' => ['small' => 'landing_page', 'large' => 'landing_page'],
    'report_form' => ['small' => 'report_form', 'large' => 'report_form'],
    'tracking_no_ticket' => ['small' => 'tracking_no_ticket', 'large' => 'tracking_with_ticket'],
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
    'role' => 'Client',
    'portal_type' => 'Public (tanpa login)',
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

$outputPath = storage_path('app/client_database_audit_report.json');
file_put_contents($outputPath, json_encode($final, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "=====================================================\n";
echo "  OUTPUT SAVED\n";
echo "=====================================================\n\n";
echo "Audit selesai. Output: {$outputPath}\n";
echo "Total scenario: ".count($results)."\n";
echo "Total slow unique query: ".count($slowExplains)."\n";

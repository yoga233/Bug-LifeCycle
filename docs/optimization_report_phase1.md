# Optimization Report Phase 1 (PM Database Audit)

Date: 2026-02-20  
Scope: duplicate query reduction, topbar notification query refactor, index migration validation, edge-case re-test, and documentation hardening.

## 1) Executive Summary

- Baseline report (before optimization package): `storage/app/pm_database_audit_report.baseline.json` (`generated_at: 2026-02-20 11:08:26`)
- Final optimized report (after optimization + migration hardening): `storage/app/pm_database_audit_report.optimized_with_indexes_final.json` (`generated_at: 2026-02-20 12:19:52`)
- Optimized report without `2026_02_20_001500` indexes (controlled rollback check): `storage/app/pm_database_audit_report.optimized_without_query_optimization_indexes.json` (`generated_at: 2026-02-20 12:12:53`)

### Headline metrics (baseline -> final optimized)

- Total queries: **162 -> 165** (`+3`)
- Total time: **855.24ms -> 264.59ms** (`-590.65ms`, ~`-69.1%`)
- Unique duplicate signatures: **4 -> 2** (`-50%`)

> Note: query count naik tipis karena topbar unread-preview kini dieksekusi di lebih banyak skenario/layout via query window-function composer, tetapi waktu total turun signifikan.

---

## 2) Detailed Before/After Changes (Code + SQL)

## 2.1 Role lookup deduplication (Team user CRUD)

### Files

- `app/Http/Controllers/ProjectManager/TeamManagementController.php`

### Before pattern (duplicate lookup)

Spatie role sync memicu role lookup berulang pada store/update.

Example baseline duplicate signatures:

- `select * from roles where name = 'Programmer' and guard_name = 'web' limit 1` (PM Team Users Store, count 2)
- `select * from roles where name = 'QA' and guard_name = 'web' limit 1` (PM Team Users Update, count 2)

### After pattern

Controller resolve role satu kali, lalu kirim `Role` model ke `syncRoles`:

```php
private function resolveInternalRole(string $roleName): Role
{
    return Role::query()
        ->select(['id', 'name', 'guard_name'])
        ->where('guard_name', 'web')
        ->where('name', $roleName)
        ->firstOrFail();
}

// store/update
$selectedRole = $this->resolveInternalRole((string) $request->string('role'));
$user->syncRoles([$selectedRole]);
```

### Impact

- PM Team Users Store: `8 -> 7` queries, `65.24ms -> 6.81ms`, duplicate `1 -> 0`
- PM Team Users Update: `9 -> 8` queries, `20.29ms -> 9.10ms`, duplicate `1 -> 0`

---

## 2.2 Topbar notifications refactor (count + preview)

### Files

- `app/Providers/AppServiceProvider.php`

### New query approach

Topbar unread count + unread preview (max 5) dipenuhi oleh satu query window-function:

```php
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
```

### Example SQL (final)

```sql
select *
from (
  select n.id, n.user_id, n.related_id, n.type, n.message, n.is_read, n.created_at,
         ROW_NUMBER() OVER (ORDER BY n.created_at DESC, n.id DESC) as rn,
         COUNT(*) OVER() as unread_total
  from notifications as n
  where n.user_id = ? and n.is_read = ?
) as nq
where rn <= ?
order by rn asc;
```

### Impact (selected)

- PM Dashboard: `14 -> 15` queries, `130.43ms -> 29.30ms`
- PM Notifications Index: `3 -> 4` queries, `47.95ms -> 6.12ms`

> Query count bisa naik di route tertentu karena composer menambah preview-query, tapi latency total skenario utama turun tajam.

---

## 3) Per-Page Comparison (Baseline -> Final Optimized)

| Page | Queries Before | Queries After | Time Before (ms) | Time After (ms) | Duplicate Before | Duplicate After |
|---|---:|---:|---:|---:|---:|---:|
| PM Dashboard | 14 | 15 | 130.43 | 29.30 | 1 | 1 |
| PM Notifications Index | 3 | 4 | 47.95 | 6.12 | 0 | 0 |
| PM Team Users Store | 8 | 7 | 65.24 | 6.81 | 1 | 0 |
| PM Team Users Update | 9 | 8 | 20.29 | 9.10 | 1 | 0 |
| PM Issues Assign | 11 | 11 | 36.83 | 17.81 | 1 | 1 |
| PM Kinerja | 12 | 13 | 29.19 | 28.16 | 0 | 0 |

Reference full page deltas are extracted in `storage/app/optimization_report_data_final.json`.

---

## 4) Remaining Duplicate Signatures (Root Cause + Decision)

Final unique duplicates: **2**

## 4.1 PM Dashboard - session read duplicate

- Pattern: `select * from sessions where id = ? limit 1`
- Frequency: 2x in same request
- Measured cost (final): total ~`1.640ms` (avg ~`0.820ms`)
- Classification: framework/session lifecycle behavior (read + subsequent access), not app-level N+1.

Decision: **Keep (LOW priority)**  
Reason: very small cost, framework-level behavior, no material perf risk.

## 4.2 PM Issues Assign - duplicated notification insert

- Pattern: `insert into notifications (...)`
- Frequency: 2x in same request
- Measured cost (final): total ~`4.230ms` (avg ~`2.115ms`)

Root cause:

- `BugStatusService::transition(... 'Assigned' ...)` creates `BugStatusChanged` notification to assignee
- `BugAssignmentController::assign()` also creates `BugAssigned` notification

Decision: **Keep for now (MEDIUM priority, semantically intentional)**  
Reason: dua event notifikasi berbeda (status transition vs assignment message). Bisa di-refactor ke single consolidated event bila product ingin satu notifikasi saja.

---

## 5) Edge-Case Re-Test (After Optimization)

Comparison format: baseline (before optimization) -> final optimized.

| Scenario | Baseline Small (ms) | Baseline Large (ms) | Baseline Ratio | Optimized Small (ms) | Optimized Large (ms) | Optimized Ratio |
|---|---:|---:|---:|---:|---:|---:|
| notifications_page_1 | 6.10 | 10.59 | 1.736x | 6.63 | 5.82 | 0.878x |
| notifications_page_depth | 4.34 | 9.28 | 2.138x | 7.61 | 6.33 | 0.832x |
| kinerja_1_year | 50.81 | 44.05 | 0.867x | 29.12 | 26.43 | 0.908x |
| issues_page_1 | 21.89 | 28.39 | 1.297x | 18.66 | 30.26 | 1.622x |
| issues_page_depth | 25.37 | 23.98 | 0.945x | 18.14 | 13.93 | 0.768x |

Takeaway:

- Notifications degradation ratio improved strongly (now <1 on both tested paths).
- Issues page_1 large dataset still degrades (1.62x), candidate for next phase.

---

## 6) Index Migration: Apply/Test/Validation

Migration file:

- `database/migrations/2026_02_20_001500_add_query_optimization_indexes.php`

### Added resilience work

- Idempotent index checks (`indexExists`) before create/drop
- FK existence checks (`foreignExists`) before drop/add
- SQLite-safe behavior for tests (PRAGMA index_list, skip named FK lookup)

### Validation executed

1. `php artisan migrate:rollback --step=1`
2. `php artisan migrate`
3. `php artisan migrate:status`

Result: **PASS** (rollback/reapply successful).

### Index diff (NoIdx -> WithIdx)

- `users`: + `users_is_active_idx`
- `bugs`: + `bugs_priority_status_idx`, `bugs_severity_status_idx`, `bugs_status_created_at_idx`
- `notifications`: + `notifications_user_created_at_idx`, `notifications_user_is_read_idx`

---

## 7) Index Impact Check (Controlled)

Controlled pair:

- NoIdx optimized snapshot: `storage/app/pm_database_audit_report.optimized_without_query_optimization_indexes.json`
- WithIdx final snapshot: `storage/app/pm_database_audit_report.optimized_with_indexes_final.json`

Aggregate:

- Queries: `165 -> 165` (expected unchanged)
- Time: `264.26ms -> 264.59ms` (delta +`0.33ms`, statistically negligible in this single-run sample)

Interpretation:

- Indexes are correctly applied and valid.
- In this run, query-level optimization contributed most of the gain; index benefit is not dominant at this dataset size and run variance.

---

## 8) Top 10 Slowest Queries (Final) + EXPLAIN

Source:

- `storage/app/top10_slowest_with_explain_final.json`

| Rank | Route | Time (ms) | Query (short) | Explain Key Summary |
|---:|---|---:|---|---|
| 1 | PM Notifications Mark All Read | 7.74 | `update notifications set is_read=1 where user_id=? and is_read=?` | key=`notifications_user_id_index`, type=`range` |
| 2 | PM Dashboard | 6.86 | `insert into sessions (...)` | write op (INSERT) |
| 3 | PM Kinerja | 6.14 | `count(*) join bug_status_histories + bugs` | key=`bsh_new_status_changed_at_idx`, `PRIMARY` on bugs |
| 4 | PM Issues Show | 5.57 | `select attachments by bug_id` | key=`attachments_bug_id_index` |
| 5 | PM Team Users Destroy | 5.47 | `delete from users where id=?` | key=`PRIMARY` |
| 6 | PM Issues Show | 4.32 | `select bug_status_histories by bug_id order by changed_at` | key=`bug_status_histories_bug_id_index`, filesort |
| 7 | PM Issues Show | 4.03 | `select comments by bug_id order by created_at` | key=`comments_bug_id_index`, filesort |
| 8 | PM Kinerja | 3.38 | `count(histories.id) join bugs` | key=`bsh_new_status_changed_at_idx` |
| 9 | PM Issues Show | 3.06 | `active programmers list with exists role_user` | materialized subquery + filesort |
| 10 | PM Kinerja | 2.86 | `group by assignee_id over date range` | key=`bsh_new_status_changed_at_idx`, temporary |

---

## 9) Test & Regression Gate

Command:

```bash
php artisan test --filter=TeamManagementUserCrudTest
```

Result: **PASS** (`2 tests`, `9 assertions`).

Note: migration was updated to remain sqlite-compatible for in-memory test suite.

---

## 10) Artifacts Produced

- `storage/app/pm_database_audit_report.baseline.json`
- `storage/app/pm_database_audit_report.optimized_without_query_optimization_indexes.json`
- `storage/app/pm_database_audit_report.optimized_with_indexes_final.json`
- `storage/app/optimization_report_data_final.json`
- `storage/app/top10_slowest_with_explain_final.json`
- `docs/optimization_report_phase1.md` (this report)

---

## 11) Final Status vs Requested Gaps

- [x] Before/after per-page comparison documented
- [x] Remaining duplicates analyzed with root cause and decision
- [x] Edge-case re-test documented (baseline vs optimized)
- [x] Index migration applied, rollback/re-apply tested, status validated
- [x] Top 10 slowest SQL with EXPLAIN documented
- [x] Regression test re-run after migration hardening

## Next recommended phase

1. Consolidate assignment-related notifications (if product agrees single event)
2. Reduce issues-page degradation on large dataset (`issues_page_1`)
3. Optimize filesort-heavy history/comments timeline queries (composite order-support indexes if needed)

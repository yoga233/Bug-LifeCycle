<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! $this->indexExists('users', 'users_is_active_idx')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('is_active', 'users_is_active_idx');
            });
        }

        // QA queue & general timeline (status + latest)
        if (! $this->indexExists('bugs', 'bugs_status_created_at_idx')) {
            Schema::table('bugs', function (Blueprint $table) {
                $table->index(['status', 'created_at'], 'bugs_status_created_at_idx');
            });
        }

        // PM dashboard filters (severity/priority + status)
        if (! $this->indexExists('bugs', 'bugs_severity_status_idx')) {
            Schema::table('bugs', function (Blueprint $table) {
                $table->index(['severity_id', 'status'], 'bugs_severity_status_idx');
            });
        }

        if (! $this->indexExists('bugs', 'bugs_priority_status_idx')) {
            Schema::table('bugs', function (Blueprint $table) {
                $table->index(['priority_id', 'status'], 'bugs_priority_status_idx');
            });
        }

        // Notification list ordering by user and latest created_at
        if (! $this->indexExists('notifications', 'notifications_user_created_at_idx')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['user_id', 'created_at'], 'notifications_user_created_at_idx');
            });
        }

        // Fast unread counters per user
        if (! $this->indexExists('notifications', 'notifications_user_is_read_idx')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['user_id', 'is_read'], 'notifications_user_is_read_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->indexExists('notifications', 'notifications_user_is_read_idx')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropIndex('notifications_user_is_read_idx');
            });
        }

        if ($this->indexExists('notifications', 'notifications_user_created_at_idx')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropIndex('notifications_user_created_at_idx');
            });
        }

        // `bugs_priority_status_idx` and `bugs_severity_status_idx` can be used by
        // existing FK constraints (priority_id, severity_id). We temporarily drop
        // those FK constraints so the composite indexes can be removed safely.
        if ($this->foreignExists('bugs', 'bugs_priority_id_foreign') || $this->foreignExists('bugs', 'bugs_severity_id_foreign')) {
            Schema::table('bugs', function (Blueprint $table) {
                if ($this->foreignExists('bugs', 'bugs_priority_id_foreign')) {
                    $table->dropForeign('bugs_priority_id_foreign');
                }

                if ($this->foreignExists('bugs', 'bugs_severity_id_foreign')) {
                    $table->dropForeign('bugs_severity_id_foreign');
                }
            });
        }

        if ($this->indexExists('bugs', 'bugs_priority_status_idx')) {
            Schema::table('bugs', function (Blueprint $table) {
                $table->dropIndex('bugs_priority_status_idx');
            });
        }

        if ($this->indexExists('bugs', 'bugs_severity_status_idx')) {
            Schema::table('bugs', function (Blueprint $table) {
                $table->dropIndex('bugs_severity_status_idx');
            });
        }

        if ($this->indexExists('bugs', 'bugs_status_created_at_idx')) {
            Schema::table('bugs', function (Blueprint $table) {
                $table->dropIndex('bugs_status_created_at_idx');
            });
        }

        if (! $this->foreignExists('bugs', 'bugs_severity_id_foreign') || ! $this->foreignExists('bugs', 'bugs_priority_id_foreign')) {
            Schema::table('bugs', function (Blueprint $table) {
                if (! $this->foreignExists('bugs', 'bugs_severity_id_foreign')) {
                    $table
                        ->foreign('severity_id', 'bugs_severity_id_foreign')
                        ->references('id')
                        ->on('severities')
                        ->restrictOnDelete();
                }

                if (! $this->foreignExists('bugs', 'bugs_priority_id_foreign')) {
                    $table
                        ->foreign('priority_id', 'bugs_priority_id_foreign')
                        ->references('id')
                        ->on('priorities')
                        ->nullOnDelete();
                }
            });
        }

        if ($this->indexExists('users', 'users_is_active_idx')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_is_active_idx');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA index_list('{$table}')");

            foreach ($rows as $row) {
                $name = (string) ((array) $row)['name'];
                if ($name === $indexName) {
                    return true;
                }
            }

            return false;
        }

        $database = DB::getDatabaseName();

        $row = DB::selectOne(
            <<<'SQL'
            SELECT 1
            FROM information_schema.statistics
            WHERE table_schema = ?
              AND table_name = ?
              AND index_name = ?
            LIMIT 1
            SQL,
            [$database, $table, $indexName]
        );

        return $row !== null;
    }

    private function foreignExists(string $table, string $constraintName): bool
    {
        // SQLite does not expose named FK constraints like MySQL/PostgreSQL.
        // For test environments (sqlite in-memory), skip named FK checks.
        if (DB::getDriverName() === 'sqlite') {
            return false;
        }

        $database = DB::getDatabaseName();

        $row = DB::selectOne(
            <<<'SQL'
            SELECT 1
            FROM information_schema.table_constraints
            WHERE table_schema = ?
              AND table_name = ?
              AND constraint_name = ?
              AND constraint_type = 'FOREIGN KEY'
            LIMIT 1
            SQL,
            [$database, $table, $constraintName]
        );

        return $row !== null;
    }
};

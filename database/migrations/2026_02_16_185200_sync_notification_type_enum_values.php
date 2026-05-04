<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function supportsEnumAlter(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! $this->supportsEnumAlter()) {
            return;
        }

        DB::statement(
            "ALTER TABLE notifications MODIFY COLUMN type ENUM('BugAssigned','BugStatusChanged','BugCommented','BugRejected','BugReported','BugDone') NOT NULL"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->supportsEnumAlter()) {
            return;
        }

        DB::table('notifications')
            ->whereIn('type', ['BugReported', 'BugDone'])
            ->update(['type' => 'BugStatusChanged']);

        DB::statement(
            "ALTER TABLE notifications MODIFY COLUMN type ENUM('BugAssigned','BugStatusChanged','BugCommented','BugRejected') NOT NULL"
        );
    }
};

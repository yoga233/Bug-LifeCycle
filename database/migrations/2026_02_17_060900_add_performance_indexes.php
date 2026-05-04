<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bug_status_histories', function (Blueprint $table) {
            $table->index('changed_at', 'bsh_changed_at_idx');
            $table->index(['new_status', 'changed_at'], 'bsh_new_status_changed_at_idx');
            $table->index(['bug_id', 'new_status', 'changed_at'], 'bsh_bug_status_changed_at_idx');
        });

        Schema::table('bugs', function (Blueprint $table) {
            $table->index(['assignee_id', 'status'], 'bugs_assignee_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bug_status_histories', function (Blueprint $table) {
            $table->dropIndex('bsh_bug_status_changed_at_idx');
            $table->dropIndex('bsh_new_status_changed_at_idx');
            $table->dropIndex('bsh_changed_at_idx');
        });

        Schema::table('bugs', function (Blueprint $table) {
            $table->dropIndex('bugs_assignee_status_idx');
        });
    }
};

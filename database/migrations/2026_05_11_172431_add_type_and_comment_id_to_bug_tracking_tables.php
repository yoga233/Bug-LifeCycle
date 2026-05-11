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
        if (!Schema::hasColumn('comments', 'type')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->string('type', 20)->default('discussion')->after('content');
            });
        }

        if (!Schema::hasColumn('attachments', 'comment_id')) {
            Schema::table('attachments', function (Blueprint $table) {
                $table->foreignId('comment_id')->nullable()->after('bug_id')->constrained('comments')->onDelete('set null');
            });
        }

        // Data migration: update existing rejection comments
        DB::table('comments')
            ->where('content', 'like', '[QA Dikembalikan]%')
            ->update([
                'type' => 'rejection',
                'content' => DB::raw("REPLACE(content, '[QA Dikembalikan] ', '')")
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('attachments', 'comment_id')) {
            Schema::table('attachments', function (Blueprint $table) {
                $table->dropForeign(['comment_id']);
                $table->dropColumn('comment_id');
            });
        }

        if (Schema::hasColumn('comments', 'type')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};

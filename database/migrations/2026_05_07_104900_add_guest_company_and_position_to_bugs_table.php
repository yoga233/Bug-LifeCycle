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
        Schema::table('bugs', function (Blueprint $table) {
            $table->string('guest_company', 150)->nullable()->after('guest_version');
            $table->string('guest_position', 100)->nullable()->after('guest_company');
        });

        if (Schema::hasTable('guest_bug_reports')) {
            Schema::table('guest_bug_reports', function (Blueprint $table) {
                $table->string('guest_company', 150)->nullable()->after('guest_version');
                $table->string('guest_position', 100)->nullable()->after('guest_company');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->dropColumn(['guest_company', 'guest_position']);
        });

        if (Schema::hasTable('guest_bug_reports')) {
            Schema::table('guest_bug_reports', function (Blueprint $table) {
                $table->dropColumn(['guest_company', 'guest_position']);
            });
        }
    }
};

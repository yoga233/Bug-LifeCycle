<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('priorities', function (Blueprint $table) {
            $table->string('bg_color', 20)->nullable()->after('sla_hours');
            $table->string('text_color', 20)->nullable()->after('bg_color');
        });

        // Seed default pastel colors for known levels (safe no-op for other levels).
        $defaults = [
            'Urgent' => ['bg_color' => '#FEE2E2', 'text_color' => '#DC2626'],
            'High' => ['bg_color' => '#FEF3C7', 'text_color' => '#D97706'],
            'Medium' => ['bg_color' => '#DBEAFE', 'text_color' => '#2563EB'],
            'Low' => ['bg_color' => '#F3F4F6', 'text_color' => '#6B7280'],
        ];

        foreach ($defaults as $level => $colors) {
            DB::table('priorities')
                ->where('level', $level)
                ->update($colors);
        }
    }

    public function down(): void
    {
        Schema::table('priorities', function (Blueprint $table) {
            $table->dropColumn(['bg_color', 'text_color']);
        });
    }
};

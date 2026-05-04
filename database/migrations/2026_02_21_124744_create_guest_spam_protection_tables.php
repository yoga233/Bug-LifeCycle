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
        // Table untuk rate limiting berbasis database (untuk guest yang tidak login)
        Schema::create('guest_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->index();
            $table->string('email')->nullable()->index();
            $table->unsignedInteger('report_count')->default(1);
            $table->timestamp('first_report_at')->useCurrent();
            $table->timestamp('last_report_at')->useCurrent();
            $table->boolean('is_blocked')->default(false)->index();
            $table->timestamp('blocked_until')->nullable();
            $table->string('block_reason')->nullable();
            $table->timestamps();

            $table->unique(['ip_address', 'email']);
        });

        // Table sementara untuk antrian laporan bug dari guest
        Schema::create('guest_bug_reports', function (Blueprint $table) {
            $table->id();
            $table->string('ticket', 20)->unique()->index();
            
            $table->string('guest_name', 100);
            $table->string('guest_email', 255);
            $table->string('guest_version', 50);
            
            $table->unsignedInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->unsignedInteger('severity_id');
            $table->foreign('severity_id')->references('id')->on('severities')->onDelete('cascade');
            
            $table->string('title', 255);
            $table->text('description');
            $table->text('reproduction_steps')->nullable();
            $table->enum('frequency', ['once', 'rare', 'frequent', 'random']);
            
            $table->enum('queue_status', ['pending', 'approved', 'rejected', 'expired'])->default('pending')->index();
            $table->text('pm_notes')->nullable();
            
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('reported_at')->useCurrent()->index();
            $table->timestamp('processed_at')->nullable();
            
            $table->timestamps();

            $table->index(['queue_status', 'reported_at']);
            $table->index(['guest_email', 'reported_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_bug_reports');
        Schema::dropIfExists('guest_rate_limits');
    }
};

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
        Schema::create('bugs', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('project_id');
            $table->unsignedInteger('severity_id');
            $table->unsignedInteger('priority_id')->nullable();
            $table->unsignedBigInteger('assignee_id')->nullable();

            $table->string('guest_name', 100);
            $table->string('guest_email', 255);
            $table->string('guest_version', 50);

            $table->string('title', 255);
            $table->text('description');
            $table->string('frequency', 50);

            $table->enum('status', [
                'Reported',
                'Assigned',
                'In Progress',
                'Testing',
                'Resolved',
                'Closed',
                'Rejected',
            ])->default('Reported');

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('assignee_id');
            $table->index('project_id');

            $table
                ->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->restrictOnDelete();

            $table
                ->foreign('severity_id')
                ->references('id')
                ->on('severities')
                ->restrictOnDelete();

            $table
                ->foreign('priority_id')
                ->references('id')
                ->on('priorities')
                ->nullOnDelete();

            $table
                ->foreign('assignee_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bugs');
    }
};

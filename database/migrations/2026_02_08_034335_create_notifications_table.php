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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('related_id');
            $table->enum('type', ['BugAssigned', 'BugStatusChanged', 'BugCommented', 'BugRejected', 'BugReported', 'BugDone']);
            $table->string('message', 255);
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('related_id');
            $table->index('is_read');

            $table
                ->foreign('related_id')
                ->references('id')
                ->on('bugs')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

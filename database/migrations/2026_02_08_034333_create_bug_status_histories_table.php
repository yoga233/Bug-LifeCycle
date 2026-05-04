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
        Schema::create('bug_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bug_id')->constrained('bugs')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('old_status', 50);
            $table->string('new_status', 50);
            $table->timestamp('changed_at')->useCurrent();

            $table->index('bug_id');
            $table->index('user_id');

            $table
                ->foreign('user_id')
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
        Schema::dropIfExists('bug_status_histories');
    }
};

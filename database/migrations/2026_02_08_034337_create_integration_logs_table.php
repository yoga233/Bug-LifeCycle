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
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bug_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('status', ['Success', 'Failed', 'Pending']);
            $table->string('response_code', 10);
            $table->text('response_body')->nullable();
            $table->timestamp('attempted_at')->useCurrent();

            $table->index('bug_id');
            $table->index('user_id');
            $table->index('status');

            $table
                ->foreign('bug_id')
                ->references('id')
                ->on('bugs')
                ->nullOnDelete();

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
        Schema::dropIfExists('integration_logs');
    }
};

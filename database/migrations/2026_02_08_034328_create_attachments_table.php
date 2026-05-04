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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bug_id')->constrained('bugs')->cascadeOnDelete();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('file_path', 255);
            $table->string('file_name', 255);
            $table->string('file_type', 50);
            $table->integer('file_size'); // kilobytes
            $table->timestamp('created_at')->useCurrent();

            $table->index('bug_id');
            $table->index('uploaded_by');

            $table
                ->foreign('uploaded_by')
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
        Schema::dropIfExists('attachments');
    }
};

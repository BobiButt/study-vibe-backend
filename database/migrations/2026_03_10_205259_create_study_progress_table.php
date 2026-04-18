<?php
// database/migrations/2024_xx_xx_xxxxxx_create_study_progress_table.php

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
        Schema::create('study_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('topic_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('note_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('progress_percentage')->default(0);
            $table->timestamp('last_studied_at')->nullable();
            $table->integer('study_time_minutes')->default(0); // Total time spent
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'subject_id']);
            $table->index(['user_id', 'topic_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_progress');
    }
};
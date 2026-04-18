<?php
// database/migrations/2024_xx_xx_xxxxxx_create_notes_table.php

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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->longText('content'); // Stores markdown/LaTeX content
            $table->text('excerpt')->nullable(); // Short preview
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('topic_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('program_id')->nullable()->constrained()->onDelete('set null');
            
            // Categorization
            $table->enum('level', ['school', 'college', 'university'])->default('university');
            $table->string('grade_level')->nullable(); // For school (6th, 7th, etc.)
            $table->integer('semester')->nullable(); // For college/university
            
            // AI Generation fields
            $table->boolean('is_ai_generated')->default(false);
            $table->text('ai_prompt')->nullable(); // Original prompt used
            $table->string('ai_model')->nullable(); // GPT-5.1, DeepSeek, etc.
            $table->json('ai_metadata')->nullable(); // Store generation parameters
            
            // Privacy and sharing
            $table->boolean('is_public')->default(false);
            $table->timestamp('published_at')->nullable();
            
            // Stats
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('downloads_count')->default(0);
            $table->float('average_rating')->default(0);
            $table->integer('reviews_count')->default(0);
            
            // Metadata
            $table->json('metadata')->nullable(); // Store additional data
            $table->boolean('is_featured')->default(false);
            $table->softDeletes(); // Allow soft delete
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'is_public']);
            $table->index(['level', 'grade_level']);
            $table->index(['program_id', 'semester']);
            $table->index(['subject_id', 'topic_id']);
            $table->index('average_rating');
            $table->index('views_count');
            $table->index('created_at');
            
            // Full text search
            $table->fullText(['title', 'content', 'excerpt']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
<?php
// database/migrations/2024_xx_xx_xxxxxx_create_subjects_table.php

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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('program_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('semester')->nullable(); // For university/college
            $table->string('grade_level')->nullable(); // For school (6th, 7th, etc.)
            $table->enum('level', ['school', 'college', 'university']);
            $table->integer('chapters_count')->default(0);
            $table->integer('notes_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
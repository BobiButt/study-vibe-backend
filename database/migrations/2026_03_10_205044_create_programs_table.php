<?php
// database/migrations/2024_xx_xx_xxxxxx_create_programs_table.php

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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // BSCS, BBA, etc.
            $table->string('full_name'); // Bachelor of Science in Computer Science
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->string('duration')->default('4 Years');
            $table->integer('semesters')->default(8);
            $table->enum('level', ['school', 'college', 'university'])->default('university');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
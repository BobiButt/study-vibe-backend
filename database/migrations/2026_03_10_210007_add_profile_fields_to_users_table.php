<?php
// database/migrations/2024_xx_xx_xxxxxx_add_profile_fields_to_users_table.php

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
        Schema::table('users', function (Blueprint $table) {
            // Add new columns to existing users table
            $table->string('username')->unique()->nullable()->after('email');
            $table->string('profile_photo')->nullable()->after('password');
            $table->string('cloudinary_public_id')->nullable()->after('profile_photo');
            $table->text('bio')->nullable()->after('cloudinary_public_id');
            $table->string('university')->nullable()->after('bio');
            $table->string('program')->nullable()->after('university');
            $table->integer('semester')->nullable()->after('program');
            $table->string('grade_level')->nullable()->after('semester');
            $table->enum('education_level', ['school', 'college', 'university'])->default('university')->after('grade_level');
            $table->integer('study_streak')->default(0)->after('education_level');
            $table->timestamp('last_active_at')->nullable()->after('study_streak');
            $table->json('preferences')->nullable()->after('last_active_at');
            $table->string('google_id')->nullable()->after('preferences');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop all added columns if we rollback
            $table->dropColumn([
                'username',
                'profile_photo',
                'cloudinary_public_id',
                'bio',
                'university',
                'program',
                'semester',
                'grade_level',
                'education_level',
                'study_streak',
                'last_active_at',
                'preferences',
                'google_id'
            ]);
        });
    }
};
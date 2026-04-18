<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_avatar_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Make password nullable for social login users
            $table->string('password')->nullable()->change();
            
            // Add social login fields
            $table->string('google_id')->nullable()->unique()->after('email');
            $table->string('google_avatar_url')->nullable()->after('google_id');
            $table->string('google_token')->nullable()->after('google_avatar_url');
            $table->string('google_refresh_token')->nullable()->after('google_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable(false)->change();
            $table->dropColumn([
                'google_id',
                'google_avatar_url',
                'google_token',
                'google_refresh_token'
            ]);
        });
    }
};
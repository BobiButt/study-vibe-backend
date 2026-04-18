<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProgramSeeder::class,
            SubjectSeeder::class,
            NoteSeeder::class,
            // We'll add more seeders here
        ]);
    }
}
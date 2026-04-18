<?php
// database/seeders/SubjectSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Program;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        // Get programs
        $bscs = Program::where('name', 'BSCS')->first();
        $bsit = Program::where('name', 'BSIT')->first();
        $bba = Program::where('name', 'BBA')->first();

        // BSCS Subjects
        $bscsSubjects = [
            ['name' => 'Programming Fundamentals', 'icon' => '💻', 'color' => 'bg-blue-500', 'semester' => 1, 'level' => 'university'],
            ['name' => 'Data Structures & Algorithms', 'icon' => '🔄', 'color' => 'bg-green-500', 'semester' => 3, 'level' => 'university'],
            ['name' => 'Database Systems', 'icon' => '🗄️', 'color' => 'bg-purple-500', 'semester' => 4, 'level' => 'university'],
            ['name' => 'Artificial Intelligence', 'icon' => '🧠', 'color' => 'bg-orange-500', 'semester' => 6, 'level' => 'university'],
            ['name' => 'Web Development', 'icon' => '🌐', 'color' => 'bg-red-500', 'semester' => 5, 'level' => 'university'],
            ['name' => 'Operating Systems', 'icon' => '⚙️', 'color' => 'bg-yellow-500', 'semester' => 4, 'level' => 'university'],
            ['name' => 'Computer Networks', 'icon' => '🔌', 'color' => 'bg-indigo-500', 'semester' => 5, 'level' => 'university'],
            ['name' => 'Software Engineering', 'icon' => '📱', 'color' => 'bg-pink-500', 'semester' => 6, 'level' => 'university'],
        ];

        foreach ($bscsSubjects as $subject) {
            $subject['program_id'] = $bscs->id;
            Subject::create($subject);
        }

        // BSIT Subjects
        $bsitSubjects = [
            ['name' => 'Computer Networks', 'icon' => '🌐', 'color' => 'bg-green-500', 'semester' => 4, 'level' => 'university'],
            ['name' => 'Cybersecurity', 'icon' => '🔒', 'color' => 'bg-red-500', 'semester' => 6, 'level' => 'university'],
            ['name' => 'Cloud Computing', 'icon' => '☁️', 'color' => 'bg-blue-500', 'semester' => 7, 'level' => 'university'],
            ['name' => 'Web Technologies', 'icon' => '💻', 'color' => 'bg-purple-500', 'semester' => 3, 'level' => 'university'],
            ['name' => 'Database Administration', 'icon' => '🗄️', 'color' => 'bg-orange-500', 'semester' => 4, 'level' => 'university'],
        ];

        foreach ($bsitSubjects as $subject) {
            $subject['program_id'] = $bsit->id;
            Subject::create($subject);
        }

        // BBA Subjects
        $bbaSubjects = [
            ['name' => 'Principles of Management', 'icon' => '📋', 'color' => 'bg-purple-500', 'semester' => 1, 'level' => 'university'],
            ['name' => 'Marketing Management', 'icon' => '📢', 'color' => 'bg-orange-500', 'semester' => 3, 'level' => 'university'],
            ['name' => 'Financial Accounting', 'icon' => '💰', 'color' => 'bg-green-500', 'semester' => 2, 'level' => 'university'],
            ['name' => 'Human Resource Management', 'icon' => '👥', 'color' => 'bg-blue-500', 'semester' => 5, 'level' => 'university'],
            ['name' => 'Business Economics', 'icon' => '📊', 'color' => 'bg-red-500', 'semester' => 3, 'level' => 'university'],
        ];

        foreach ($bbaSubjects as $subject) {
            $subject['program_id'] = $bba->id;
            Subject::create($subject);
        }

        $this->command->info('Subjects seeded successfully!');
    }
}
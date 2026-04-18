<?php
// database/seeders/ProgramSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            // ==================== COMPUTER SCIENCE & IT ====================
            [
                'name' => 'BSCS',
                'full_name' => 'Bachelor of Science in Computer Science',
                'icon' => '💻',
                'color' => 'bg-blue-500',
                'description' => 'Programming, Algorithms, AI, Web Development, Software Engineering',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],
            [
                'name' => 'BSIT',
                'full_name' => 'Bachelor of Science in Information Technology',
                'icon' => '🌐',
                'color' => 'bg-green-500',
                'description' => 'Networks, Databases, Security, Cloud Computing, Web Technologies',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],
            [
                'name' => 'BSSE',
                'full_name' => 'Bachelor of Science in Software Engineering',
                'icon' => '📱',
                'color' => 'bg-purple-500',
                'description' => 'Software Design, Development, Testing, Project Management',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],
            [
                'name' => 'BSAI',
                'full_name' => 'Bachelor of Science in Artificial Intelligence',
                'icon' => '🧠',
                'color' => 'bg-indigo-500',
                'description' => 'Machine Learning, Deep Learning, NLP, Computer Vision',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],
            [
                'name' => 'BSDS',
                'full_name' => 'Bachelor of Science in Data Science',
                'icon' => '📊',
                'color' => 'bg-teal-500',
                'description' => 'Big Data, Analytics, Statistics, Data Mining',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],
            [
                'name' => 'BSCY',
                'full_name' => 'Bachelor of Science in Cybersecurity',
                'icon' => '🔒',
                'color' => 'bg-red-500',
                'description' => 'Network Security, Cryptography, Ethical Hacking, Forensics',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],

            // ==================== BUSINESS & MANAGEMENT ====================
            [
                'name' => 'BBA',
                'full_name' => 'Bachelor of Business Administration',
                'icon' => '📊',
                'color' => 'bg-purple-500',
                'description' => 'Marketing, Finance, HR, Entrepreneurship, Management',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],
            [
                'name' => 'BSAF',
                'full_name' => 'Bachelor of Science in Accounting & Finance',
                'icon' => '💰',
                'color' => 'bg-green-500',
                'description' => 'Financial Accounting, Auditing, Taxation, Corporate Finance',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],
            [
                'name' => 'BSEF',
                'full_name' => 'Bachelor of Science in Economics & Finance',
                'icon' => '📈',
                'color' => 'bg-orange-500',
                'description' => 'Microeconomics, Macroeconomics, Financial Markets, Banking',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],
            [
                'name' => 'BSM',
                'full_name' => 'Bachelor of Science in Marketing',
                'icon' => '📢',
                'color' => 'bg-pink-500',
                'description' => 'Digital Marketing, Brand Management, Consumer Behavior',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],

            // ==================== ENGINEERING ====================
            [
                'name' => 'BSCE',
                'full_name' => 'Bachelor of Science in Computer Engineering',
                'icon' => '⚙️',
                'color' => 'bg-cyan-500',
                'description' => 'Computer Architecture, Embedded Systems, Hardware Design',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],
            [
                'name' => 'BSEE',
                'full_name' => 'Bachelor of Science in Electrical Engineering',
                'icon' => '⚡',
                'color' => 'bg-yellow-500',
                'description' => 'Power Systems, Electronics, Telecommunications',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],
            [
                'name' => 'BSME',
                'full_name' => 'Bachelor of Science in Mechanical Engineering',
                'icon' => '🔧',
                'color' => 'bg-gray-500',
                'description' => 'Thermodynamics, Fluid Mechanics, Manufacturing',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],

            // ==================== SCIENCES ====================
            [
                'name' => 'BSPH',
                'full_name' => 'Bachelor of Science in Physics',
                'icon' => '🔭',
                'color' => 'bg-blue-500',
                'description' => 'Quantum Mechanics, Thermodynamics, Electromagnetism',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],
            [
                'name' => 'BSCH',
                'full_name' => 'Bachelor of Science in Chemistry',
                'icon' => '🧪',
                'color' => 'bg-green-500',
                'description' => 'Organic Chemistry, Inorganic Chemistry, Biochemistry',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],
            [
                'name' => 'BSBI',
                'full_name' => 'Bachelor of Science in Biology',
                'icon' => '🧬',
                'color' => 'bg-emerald-500',
                'description' => 'Molecular Biology, Genetics, Zoology, Botany',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],
            [
                'name' => 'BSMT',
                'full_name' => 'Bachelor of Science in Mathematics',
                'icon' => '📐',
                'color' => 'bg-orange-500',
                'description' => 'Calculus, Algebra, Statistics, Numerical Analysis',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],

            // ==================== ARTS & HUMANITIES ====================
            [
                'name' => 'BAENG',
                'full_name' => 'Bachelor of Arts in English',
                'icon' => '📚',
                'color' => 'bg-purple-500',
                'description' => 'Literature, Linguistics, Creative Writing',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],
            [
                'name' => 'BAECO',
                'full_name' => 'Bachelor of Arts in Economics',
                'icon' => '📉',
                'color' => 'bg-blue-500',
                'description' => 'Microeconomics, Macroeconomics, Development Economics',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],
            [
                'name' => 'BAPS',
                'full_name' => 'Bachelor of Arts in Political Science',
                'icon' => '🏛️',
                'color' => 'bg-red-500',
                'description' => 'Political Theory, International Relations, Public Administration',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],
            [
                'name' => 'BASO',
                'full_name' => 'Bachelor of Arts in Sociology',
                'icon' => '👥',
                'color' => 'bg-teal-500',
                'description' => 'Social Structures, Cultural Studies, Research Methods',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],
            [
                'name' => 'BAPSY',
                'full_name' => 'Bachelor of Arts in Psychology',
                'icon' => '🧠',
                'color' => 'bg-pink-500',
                'description' => 'Clinical Psychology, Cognitive Psychology, Behavioral Studies',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],

            // ==================== COMMERCE ====================
            [
                'name' => 'BCOM',
                'full_name' => 'Bachelor of Commerce',
                'icon' => '💼',
                'color' => 'bg-blue-500',
                'description' => 'Accounting, Business Law, Taxation, Auditing',
                'duration' => '4 Years',
                'semesters' => 8,
                'level' => 'university',
                'is_active' => true
            ],

            // ==================== LAW ====================
            [
                'name' => 'LLB',
                'full_name' => 'Bachelor of Laws',
                'icon' => '⚖️',
                'color' => 'bg-purple-500',
                'description' => 'Constitutional Law, Criminal Law, Civil Law, Legal Practice',
                'duration' => '5 Years',
                'semesters' => 10,
                'level' => 'university',
                'is_active' => true
            ],
        ];

        foreach ($programs as $program) {
            Program::updateOrCreate(
                ['name' => $program['name']],
                $program
            );
        }

        $this->command->info('Programs seeded successfully!');
    }
}
<?php
// database/seeders/NoteSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Note;
use App\Models\User;
use App\Models\Subject;
use App\Models\Review;
use App\Models\Program;

class NoteSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing data
        $user = User::first(); // Get first user (Bobi Butt)
        $bscs = Program::where('name', 'BSCS')->first();
        $subjects = Subject::where('program_id', $bscs->id)->get();
        
        if (!$user) {
            $this->command->info('No users found. Please create a user first.');
            return;
        }

        $this->command->info('Creating sample notes...');

        // Sample AI-generated notes for BSCS
        $notes = [
            [
                'title' => 'Binary Search Algorithm - Complete Guide',
                'content' => "# Binary Search Algorithm\n\nBinary search is an efficient algorithm for finding an item from a sorted list of items. It works by repeatedly dividing in half the portion of the list that could contain the item, until you've narrowed down the possible locations to just one.\n\n## How It Works\n\n1. Start with the middle element of the sorted array\n2. If the target equals the middle element, return its index\n3. If target is less than middle, search left half\n4. If target is greater than middle, search right half\n5. Repeat until found or subarray size becomes 0\n\n## Time Complexity\n- Best Case: O(1)\n- Average Case: O(log n)\n- Worst Case: O(log n)\n\n## Space Complexity\n- O(1) for iterative implementation\n- O(log n) for recursive implementation\n\n## Example Code\n```python\ndef binary_search(arr, target):\n    left, right = 0, len(arr) - 1\n    \n    while left <= right:\n        mid = (left + right) // 2\n        \n        if arr[mid] == target:\n            return mid\n        elif arr[mid] < target:\n            left = mid + 1\n        else:\n            right = mid - 1\n    \n    return -1\n```",
                'excerpt' => 'Binary search is an efficient algorithm for finding an item from a sorted list...',
                'subject_id' => $subjects->where('name', 'Data Structures & Algorithms')->first()?->id,
                'program_id' => $bscs->id,
                'level' => 'university',
                'semester' => 3,
                'is_ai_generated' => true,
                'ai_model' => 'GPT-5.1',
                'is_public' => true,
                'views_count' => 234,
                'likes_count' => 45,
                'average_rating' => 4.9,
                'reviews_count' => 0,
            ],
            [
                'title' => 'Database Normalization: 1NF, 2NF, 3NF Explained',
                'content' => "# Database Normalization\n\nDatabase normalization is the process of organizing data in a database to reduce redundancy and improve integrity.\n\n## First Normal Form (1NF)\n- Each table cell should contain a single value\n- Each record needs to be unique\n\n## Second Normal Form (2NF)\n- Be in 1NF\n- Remove partial dependencies\n\n## Third Normal Form (3NF)\n- Be in 2NF\n- Remove transitive dependencies\n\n## Example\nConsider a table with StudentID, Course, Instructor. This violates 2NF because Instructor depends on Course, not StudentID. Solution: Split into Students, Courses, and Instructors tables.",
                'excerpt' => 'Database normalization is the process of organizing data to reduce redundancy...',
                'subject_id' => $subjects->where('name', 'Database Systems')->first()?->id,
                'program_id' => $bscs->id,
                'level' => 'university',
                'semester' => 4,
                'is_ai_generated' => true,
                'ai_model' => 'Claude 4.5',
                'is_public' => true,
                'views_count' => 567,
                'likes_count' => 89,
                'average_rating' => 4.8,
                'reviews_count' => 0,
            ],
            [
                'title' => 'Operating System Processes and Threads',
                'content' => "# Processes and Threads\n\n## Process\nA process is a program in execution. It has its own memory space, resources, and execution context.\n\n## Thread\nA thread is a lightweight process. Threads share the same memory space and resources of their parent process.\n\n## Differences\n| Feature | Process | Thread |\n|---------|---------|--------|\n| Memory | Separate memory space | Shared memory |\n| Creation | Slow | Fast |\n| Communication | IPC required | Direct communication |\n| Context Switch | Expensive | Less expensive |\n\n## Multithreading Benefits\n- Improved responsiveness\n- Resource sharing\n- Economy\n- Scalability",
                'excerpt' => 'Processes and threads are fundamental concepts in operating systems...',
                'subject_id' => $subjects->where('name', 'Operating Systems')->first()?->id,
                'program_id' => $bscs->id,
                'level' => 'university',
                'semester' => 4,
                'is_ai_generated' => false,
                'ai_model' => null,
                'is_public' => true,
                'views_count' => 432,
                'likes_count' => 67,
                'average_rating' => 4.7,
                'reviews_count' => 0,
            ],
            [
                'title' => 'Introduction to Machine Learning',
                'content' => "# Machine Learning Basics\n\nMachine Learning (ML) is a subset of artificial intelligence that enables systems to learn and improve from experience without being explicitly programmed.\n\n## Types of Machine Learning\n\n1. **Supervised Learning**\n   - Classification\n   - Regression\n\n2. **Unsupervised Learning**\n   - Clustering\n   - Association\n\n3. **Reinforcement Learning**\n\n## Common Algorithms\n- Linear Regression\n- Decision Trees\n- Neural Networks\n- Support Vector Machines\n\n## Applications\n- Image recognition\n- Natural language processing\n- Recommendation systems\n- Autonomous vehicles",
                'excerpt' => 'Machine Learning enables systems to learn and improve from experience...',
                'subject_id' => $subjects->where('name', 'Artificial Intelligence')->first()?->id,
                'program_id' => $bscs->id,
                'level' => 'university',
                'semester' => 6,
                'is_ai_generated' => true,
                'ai_model' => 'DeepSeek R1',
                'is_public' => true,
                'views_count' => 789,
                'likes_count' => 123,
                'average_rating' => 5.0,
                'reviews_count' => 0,
            ],
            [
                'title' => 'Web Development: RESTful APIs',
                'content' => "# RESTful APIs\n\nREST (Representational State Transfer) is an architectural style for designing networked applications.\n\n## HTTP Methods\n- **GET**: Retrieve resources\n- **POST**: Create new resources\n- **PUT**: Update resources\n- **DELETE**: Remove resources\n\n## Best Practices\n1. Use nouns for endpoints (not verbs)\n2. Use plural nouns for collections\n3. Use HTTP status codes\n4. Version your API\n5. Provide proper documentation\n\n## Example Endpoints\n```\nGET    /api/users\nPOST   /api/users\nGET    /api/users/{id}\nPUT    /api/users/{id}\nDELETE /api/users/{id}\n```\n\n## Status Codes\n- 200: Success\n- 201: Created\n- 400: Bad Request\n- 401: Unauthorized\n- 404: Not Found\n- 500: Server Error",
                'excerpt' => 'REST is an architectural style for designing networked applications...',
                'subject_id' => $subjects->where('name', 'Web Development')->first()?->id,
                'program_id' => $bscs->id,
                'level' => 'university',
                'semester' => 5,
                'is_ai_generated' => false,
                'ai_model' => null,
                'is_public' => false, // Private note
                'views_count' => 0,
                'likes_count' => 0,
                'average_rating' => 0,
                'reviews_count' => 0,
            ],
        ];

        foreach ($notes as $noteData) {
            $noteData['user_id'] = $user->id;
            Note::create($noteData);
        }

        $this->command->info('Sample notes created successfully!');

        // Add reviews for public notes (with duplicate prevention)
        $this->command->info('Adding sample reviews...');
        
        $publicNotes = Note::where('is_public', true)->get();
        $users = User::where('id', '!=', $user->id)->get(); // Exclude the note owner
        
        if ($users->count() > 0) {
            foreach ($publicNotes as $note) {
                // Add 2-3 reviews per note, using different users
                $reviewCount = min(rand(2, 3), $users->count());
                $selectedUsers = $users->random($reviewCount);
                
                foreach ($selectedUsers as $reviewUser) {
                    // Check if review already exists (prevent duplicate)
                    $existingReview = Review::where('user_id', $reviewUser->id)
                        ->where('note_id', $note->id)
                        ->first();
                    
                    if (!$existingReview) {
                        Review::create([
                            'user_id' => $reviewUser->id,
                            'note_id' => $note->id,
                            'rating' => rand(4, 5), // 4 or 5 star ratings
                            'comment' => 'Great notes! Very helpful for my studies.',
                            'is_helpful' => true
                        ]);
                    }
                }
            }
            
            // Update notes' average ratings
            foreach ($publicNotes as $note) {
                $note->updateRating();
            }
            
            $this->command->info('Sample reviews added successfully!');
        } else {
            $this->command->info('No additional users found for reviews. Skipping reviews.');
        }
    }
}
<?php
// app/Http/Controllers/API/UserProfileController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Note;
use App\Models\StudyProgress;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    /**
     * Get authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Get counts
            $notesCount = $user->notes()->count();
            $publicNotes = $user->notes()->where('is_public', true)->count();
            $privateNotes = $user->notes()->where('is_public', false)->count();
            $aiNotes = $user->notes()->where('is_ai_generated', true)->count();
            $reviewsCount = Review::where('user_id', $user->id)->count();
            $studyStreak = $user->study_streak ?? 0;
            $totalStudyHours = StudyProgress::where('user_id', $user->id)->sum('study_time_minutes') / 60;
            $completedTopics = StudyProgress::where('user_id', $user->id)
                ->where('progress_percentage', 100)
                ->count();

            // Get recent activity
            $recentActivity = [];
            
            // Recent notes created
            $recentNotes = $user->notes()
                ->with('subject:id,name')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($note) {
                    return [
                        'type' => 'note_created',
                        'title' => $note->title,
                        'note_id' => $note->id,
                        'subject' => $note->subject->name ?? null,
                        'is_public' => $note->is_public,
                        'semester' => $note->semester,
                        'time' => $note->created_at->diffForHumans(),
                        'created_at' => $note->created_at
                    ];
                });

            // Add recent notes to activity
            foreach ($recentNotes as $note) {
                $recentActivity[] = $note;
            }

            // Recent study progress
            $recentStudy = StudyProgress::where('user_id', $user->id)
                ->with(['subject:id,name', 'topic:id,name'])
                ->whereNotNull('last_studied_at')
                ->latest('last_studied_at')
                ->limit(5)
                ->get()
                ->map(function ($progress) {
                    return [
                        'type' => 'study_activity',
                        'subject' => $progress->subject->name ?? null,
                        'topic' => $progress->topic->name ?? null,
                        'progress' => $progress->progress_percentage,
                        'time' => $progress->last_studied_at->diffForHumans(),
                        'created_at' => $progress->last_studied_at
                    ];
                });

            // Add recent study to activity
            foreach ($recentStudy as $study) {
                $recentActivity[] = $study;
            }

            // Sort by created_at descending
            usort($recentActivity, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            // Take only first 10
            $recentActivity = array_slice($recentActivity, 0, 10);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'profile_photo' => $user->profile_photo,
                    'cloudinary_public_id' => $user->cloudinary_public_id,
                    'bio' => $user->bio,
                    'university' => $user->university,
                    'program' => $user->program,
                    'semester' => $user->semester,
                    'grade_level' => $user->grade_level,
                    'education_level' => $user->education_level,
                    'study_streak' => $studyStreak,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    // Stats
                    'notes_count' => $notesCount,
                    'public_notes' => $publicNotes,
                    'private_notes' => $privateNotes,
                    'ai_generated_notes' => $aiNotes,
                    'total_reviews' => $reviewsCount,
                    'total_study_hours' => round($totalStudyHours, 1),
                    'completed_topics' => $completedTopics,
                    'recent_activity' => $recentActivity
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Profile show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id,
                'bio' => 'nullable|string|max:1000',
                'university' => 'nullable|string|max:255',
                'program' => 'nullable|string|max:255',
                'semester' => 'nullable|integer|min:1|max:12',
                'grade_level' => 'nullable|string|max:50',
                'education_level' => 'sometimes|in:school,college,university',
                'profile_photo' => 'nullable|string|max:500',
                'cloudinary_public_id' => 'nullable|string|max:255',
                'preferences' => 'nullable|json'
            ]);

            $user->update($validated);

            // Return the updated user data
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'profile_photo' => $user->profile_photo,
                    'cloudinary_public_id' => $user->cloudinary_public_id,
                    'bio' => $user->bio,
                    'university' => $user->university,
                    'program' => $user->program,
                    'semester' => $user->semester,
                    'grade_level' => $user->grade_level,
                    'education_level' => $user->education_level,
                    'study_streak' => $user->study_streak,
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Profile update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user statistics.
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $stats = [
                'total_notes' => $user->notes()->count(),
                'public_notes' => $user->notes()->where('is_public', true)->count(),
                'private_notes' => $user->notes()->where('is_public', false)->count(),
                'ai_generated_notes' => $user->notes()->where('is_ai_generated', true)->count(),
                'total_reviews' => Review::where('user_id', $user->id)->count(),
                'study_streak' => $user->study_streak ?? 0,
                'total_study_hours' => round(StudyProgress::where('user_id', $user->id)->sum('study_time_minutes') / 60, 1),
                'completed_topics' => StudyProgress::where('user_id', $user->id)
                    ->where('progress_percentage', 100)
                    ->count(),
                'member_since_days' => now()->diffInDays($user->created_at)
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
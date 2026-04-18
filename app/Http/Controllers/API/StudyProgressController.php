<?php
// app/Http/Controllers/API/StudyProgressController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StudyProgress;
use App\Models\Topic;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudyProgressController extends Controller
{
    /**
     * Get user's study progress overview.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function overview(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Get overall progress
            $totalTopics = Topic::count();
            $completedTopics = StudyProgress::where('user_id', $user->id)
                ->where('progress_percentage', 100)
                ->count();

            // Get progress by subject
            $subjectProgress = Subject::withCount('topics')
                ->get()
                ->map(function ($subject) use ($user) {
                    $completedTopics = StudyProgress::where('user_id', $user->id)
                        ->whereIn('topic_id', $subject->topics->pluck('id'))
                        ->where('progress_percentage', 100)
                        ->count();
                    
                    return [
                        'subject_id' => $subject->id,
                        'subject_name' => $subject->name,
                        'total_topics' => $subject->topics_count,
                        'completed_topics' => $completedTopics,
                        'progress_percentage' => $subject->topics_count > 0 
                            ? round(($completedTopics / $subject->topics_count) * 100) 
                            : 0
                    ];
                });

            // Get recent study sessions
            $recentSessions = StudyProgress::where('user_id', $user->id)
                ->with(['subject:id,name', 'topic:id,name'])
                ->whereNotNull('last_studied_at')
                ->latest('last_studied_at')
                ->limit(10)
                ->get()
                ->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'subject' => $session->subject->name ?? null,
                        'topic' => $session->topic->name ?? null,
                        'progress' => $session->progress_percentage,
                        'study_minutes' => $session->study_time_minutes,
                        'studied_at' => $session->last_studied_at,
                        'studied_at_human' => $session->last_studied_at->diffForHumans()
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Study progress overview retrieved successfully',
                'data' => [
                    'overall' => [
                        'total_topics' => $totalTopics,
                        'completed_topics' => $completedTopics,
                        'overall_progress' => $totalTopics > 0 
                            ? round(($completedTopics / $totalTopics) * 100) 
                            : 0,
                        'study_streak' => $user->study_streak,
                        'last_active' => $user->last_active_at
                    ],
                    'subject_progress' => $subjectProgress,
                    'recent_sessions' => $recentSessions
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve study progress',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update progress for a topic.
     * 
     * @param Request $request
     * @param int $topicId
     * @return JsonResponse
     */
    public function updateTopicProgress(Request $request, int $topicId): JsonResponse
    {
        try {
            $user = $request->user();
            $topic = Topic::find($topicId);

            if (!$topic) {
                return response()->json([
                    'success' => false,
                    'message' => 'Topic not found'
                ], 404);
            }

            $validated = $request->validate([
                'progress_percentage' => 'required|integer|min:0|max:100',
                'study_time_minutes' => 'sometimes|integer|min:0'
            ]);

            // Update or create progress
            $progress = StudyProgress::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'topic_id' => $topicId,
                    'subject_id' => $topic->subject_id
                ],
                [
                    'progress_percentage' => $validated['progress_percentage'],
                    'last_studied_at' => now(),
                    'study_time_minutes' => $validated['study_time_minutes'] ?? 0
                ]
            );

            // Update user's last active and streak
            $user->updateLastActive();

            // Check if studied today for streak
            if (!$user->hasStudiedToday()) {
                $user->incrementStudyStreak();
            }

            return response()->json([
                'success' => true,
                'message' => 'Topic progress updated successfully',
                'data' => $progress
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update progress',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log study time.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logStudyTime(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'topic_id' => 'required|exists:topics,id',
                'minutes' => 'required|integer|min:1|max:480', // Max 8 hours
                'progress' => 'sometimes|integer|min:0|max:100'
            ]);

            $topic = Topic::find($validated['topic_id']);

            // Update or create progress
            $progress = StudyProgress::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'topic_id' => $validated['topic_id'],
                    'subject_id' => $topic->subject_id
                ],
                [
                    'progress_percentage' => $validated['progress'] ?? 0,
                    'last_studied_at' => now(),
                    'study_time_minutes' => $validated['minutes']
                ]
            );

            // Update user's last active
            $user->updateLastActive();

            return response()->json([
                'success' => true,
                'message' => 'Study time logged successfully',
                'data' => $progress
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log study time',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get study calendar (heatmap data).
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getStudyCalendar(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $year = $request->get('year', now()->year);
            $month = $request->get('month', now()->month);

            $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
            $endDate = now()->setYear($year)->setMonth($month)->endOfMonth();

            $studyData = StudyProgress::where('user_id', $user->id)
                ->whereBetween('last_studied_at', [$startDate, $endDate])
                ->selectRaw('DATE(last_studied_at) as date, SUM(study_time_minutes) as total_minutes, COUNT(DISTINCT topic_id) as topics_studied')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            // Generate calendar data
            $calendar = [];
            $currentDate = clone $startDate;

            while ($currentDate <= $endDate) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayData = $studyData->get($dateStr);

                $calendar[] = [
                    'date' => $dateStr,
                    'day' => $currentDate->day,
                    'month' => $currentDate->month,
                    'year' => $currentDate->year,
                    'weekday' => $currentDate->dayOfWeek,
                    'minutes' => $dayData->total_minutes ?? 0,
                    'topics' => $dayData->topics_studied ?? 0,
                    'intensity' => $this->calculateIntensity($dayData->total_minutes ?? 0)
                ];

                $currentDate->addDay();
            }

            return response()->json([
                'success' => true,
                'message' => 'Study calendar retrieved successfully',
                'data' => [
                    'year' => $year,
                    'month' => $month,
                    'calendar' => $calendar,
                    'total_minutes' => $studyData->sum('total_minutes'),
                    'total_days' => $studyData->count()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve study calendar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate intensity for heatmap (0-4 scale).
     * 
     * @param int $minutes
     * @return int
     */
    private function calculateIntensity(int $minutes): int
    {
        if ($minutes === 0) return 0;
        if ($minutes < 30) return 1;
        if ($minutes < 60) return 2;
        if ($minutes < 120) return 3;
        return 4;
    }

    /**
     * Get user's study streak details.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getStreak(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Calculate current streak
            $currentStreak = 0;
            $date = now()->startOfDay();

            while (true) {
                $studied = StudyProgress::where('user_id', $user->id)
                    ->whereDate('last_studied_at', $date)
                    ->exists();

                if (!$studied) break;

                $currentStreak++;
                $date->subDay();
            }

            // Calculate longest streak
            $longestStreak = $user->study_streak; // You might want to track this separately

            // Get streak history (last 30 days)
            $streakHistory = [];
            $date = now()->subDays(29)->startOfDay();

            for ($i = 0; $i < 30; $i++) {
                $studied = StudyProgress::where('user_id', $user->id)
                    ->whereDate('last_studied_at', $date)
                    ->exists();

                $streakHistory[] = [
                    'date' => $date->format('Y-m-d'),
                    'studied' => $studied,
                    'day' => $date->day
                ];

                $date->addDay();
            }

            return response()->json([
                'success' => true,
                'message' => 'Streak data retrieved successfully',
                'data' => [
                    'current_streak' => $currentStreak,
                    'longest_streak' => $longestStreak,
                    'streak_history' => $streakHistory,
                    'last_studied' => $user->last_active_at
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve streak data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
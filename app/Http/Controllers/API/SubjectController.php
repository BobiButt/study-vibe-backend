<?php
// app/Http/Controllers/API/SubjectController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects with optional filters.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Subject::query()->with('program');

            // Filter by level (school/college/university)
            if ($request->has('level')) {
                $query->where('level', $request->level);
            }

            // Filter by program
            if ($request->has('program_id')) {
                $query->where('program_id', $request->program_id);
            }

            // Filter by semester (for university)
            if ($request->has('semester')) {
                $query->where('semester', $request->semester);
            }

            // Filter by grade level (for school)
            if ($request->has('grade_level')) {
                $query->where('grade_level', $request->grade_level);
            }

            // Search by name
            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Get subjects with counts
            $subjects = $query->withCount(['topics', 'notes'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Subjects retrieved successfully',
                'data' => $subjects
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subjects',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified subject with its topics and notes.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $subject = Subject::with(['program', 'topics' => function ($query) {
                $query->orderBy('order');
            }])->withCount('notes')->find($id);

            if (!$subject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found'
                ], 404);
            }

            // Get recent notes for this subject
            $recentNotes = Note::where('subject_id', $id)
                ->where('is_public', true)
                ->with('user:id,name,profile_photo')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Calculate progress (if user is authenticated)
            $progress = 0;
            if (auth()->check()) {
                $completedTopics = Topic::where('subject_id', $id)
                    ->whereHas('studyProgress', function ($query) {
                        $query->where('user_id', auth()->id())
                              ->where('progress_percentage', 100);
                    })->count();
                
                $totalTopics = $subject->topics->count();
                $progress = $totalTopics > 0 ? round(($completedTopics / $totalTopics) * 100) : 0;
            }

            $responseData = [
                'id' => $subject->id,
                'name' => $subject->name,
                'icon' => $subject->icon,
                'color' => $subject->color,
                'description' => $subject->description,
                'program' => $subject->program,
                'semester' => $subject->semester,
                'grade_level' => $subject->grade_level,
                'level' => $subject->level,
                'chapters_count' => $subject->chapters_count,
                'notes_count' => $subject->notes_count,
                'topics' => $subject->topics,
                'recent_notes' => $recentNotes,
                'progress' => $progress,
                'created_at' => $subject->created_at,
                'updated_at' => $subject->updated_at,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Subject retrieved successfully',
                'data' => $responseData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get topics for a specific subject.
     * 
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function getTopics(int $id, Request $request): JsonResponse
    {
        try {
            $subject = Subject::find($id);

            if (!$subject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found'
                ], 404);
            }

            $query = Topic::where('subject_id', $id);

            // Optional filtering
            if ($request->has('completed')) {
                $completed = filter_var($request->completed, FILTER_VALIDATE_BOOLEAN);
                if (auth()->check()) {
                    // This would need a more complex query with user progress
                }
            }

            $topics = $query->orderBy('order')->get();

            // Add user progress if authenticated
            if (auth()->check()) {
                foreach ($topics as $topic) {
                    $progress = $topic->studyProgress()
                        ->where('user_id', auth()->id())
                        ->first();
                    $topic->is_completed = $progress ? $progress->progress_percentage === 100 : false;
                    $topic->progress_percentage = $progress ? $progress->progress_percentage : 0;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Topics retrieved successfully',
                'data' => $topics
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve topics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notes for a specific subject.
     * 
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotes(int $id, Request $request): JsonResponse
    {
        try {
            $subject = Subject::find($id);

            if (!$subject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found'
                ], 404);
            }

            $query = Note::where('subject_id', $id)
                ->with('user:id,name,profile_photo')
                ->withCount('reviews');

            // Filter by public/private based on authentication
            if (auth()->check()) {
                $query->where(function ($q) {
                    $q->where('is_public', true)
                      ->orWhere('user_id', auth()->id());
                });
            } else {
                $query->where('is_public', true);
            }

            // Filter by topic
            if ($request->has('topic_id')) {
                $query->where('topic_id', $request->topic_id);
            }

            // Filter by AI generated
            if ($request->has('is_ai_generated')) {
                $query->where('is_ai_generated', filter_var($request->is_ai_generated, FILTER_VALIDATE_BOOLEAN));
            }

            // Sort options
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSorts = ['created_at', 'views_count', 'average_rating', 'title'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $notes = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Notes retrieved successfully',
                'data' => $notes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created subject.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
{
    try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'program_id' => 'nullable|exists:programs,id',
            'semester' => 'nullable|integer|min:1|max:12',
            'grade_level' => 'nullable|string|max:50',
            'level' => 'required|in:school,college,university',
            'is_active' => 'boolean'
        ]);

        $subject = Subject::create($validated);

        \Log::info('Subject created:', ['id' => $subject->id, 'name' => $subject->name]);

        return response()->json([
            'success' => true,
            'message' => 'Subject created successfully',
            'data' => $subject
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Subject validation failed:', $e->errors());
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Subject creation failed:', ['error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to create subject',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Update the specified subject.
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $subject = Subject::find($id);

            if (!$subject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found'
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'icon' => 'nullable|string|max:50',
                'color' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'program_id' => 'nullable|exists:programs,id',
                'semester' => 'nullable|integer|min:1|max:12',
                'grade_level' => 'nullable|string|max:50',
                'level' => 'sometimes|in:school,college,university',
                'is_active' => 'sometimes|boolean'
            ]);

            $subject->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Subject updated successfully',
                'data' => $subject
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
                'message' => 'Failed to update subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified subject.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $subject = Subject::find($id);

            if (!$subject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found'
                ], 404);
            }

            // Check if subject has topics or notes
            if ($subject->topics()->count() > 0 || $subject->notes()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete subject with existing topics or notes'
                ], 409);
            }

            $subject->delete();

            return response()->json([
                'success' => true,
                'message' => 'Subject deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update subject progress for authenticated user.
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateProgress(Request $request, int $id): JsonResponse
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $subject = Subject::find($id);

            if (!$subject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found'
                ], 404);
            }

            $validated = $request->validate([
                'progress_percentage' => 'required|integer|min:0|max:100',
                'topic_id' => 'nullable|exists:topics,id'
            ]);

            // Update or create study progress
            $progress = $user->studyProgress()->updateOrCreate(
                [
                    'subject_id' => $id,
                    'topic_id' => $validated['topic_id'] ?? null
                ],
                [
                    'progress_percentage' => $validated['progress_percentage'],
                    'last_studied_at' => now()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Progress updated successfully',
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
}
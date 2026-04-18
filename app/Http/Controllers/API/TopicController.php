<?php
// app/Http/Controllers/API/TopicController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\Subject;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TopicController extends Controller
{
    /**
     * Display a listing of topics with filters.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Topic::query()->with('subject:id,name');

            // Filter by subject
            if ($request->has('subject_id')) {
                $query->where('subject_id', $request->subject_id);
            }

            // Search by name
            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Order by default order
            $query->orderBy('order');

            $topics = $query->withCount('notes')->get();

            // Add progress for authenticated user
            if (auth()->check()) {
                foreach ($topics as $topic) {
                    $progress = $topic->studyProgress()
                        ->where('user_id', auth()->id())
                        ->first();
                    $topic->is_completed = $progress ? $progress->progress_percentage === 100 : false;
                    $topic->user_progress = $progress ? $progress->progress_percentage : 0;
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
     * Display the specified topic with its notes.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $topic = Topic::with(['subject:id,name,program_id', 'notes' => function ($query) {
                $query->where('is_public', true)
                      ->orWhere('user_id', auth()->id())
                      ->with('user:id,name,profile_photo')
                      ->latest()
                      ->limit(10);
            }])->find($id);

            if (!$topic) {
                return response()->json([
                    'success' => false,
                    'message' => 'Topic not found'
                ], 404);
            }

            // Add user progress if authenticated
            if (auth()->check()) {
                $progress = $topic->studyProgress()
                    ->where('user_id', auth()->id())
                    ->first();
                $topic->user_progress = $progress ? $progress->progress_percentage : 0;
                $topic->is_completed = $progress ? $progress->progress_percentage === 100 : false;
            }

            return response()->json([
                'success' => true,
                'message' => 'Topic retrieved successfully',
                'data' => $topic
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve topic',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notes for a specific topic.
     * 
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotes(int $id, Request $request): JsonResponse
    {
        try {
            $topic = Topic::find($id);

            if (!$topic) {
                return response()->json([
                    'success' => false,
                    'message' => 'Topic not found'
                ], 404);
            }

            $query = Note::where('topic_id', $id)
                ->with('user:id,name,profile_photo');

            // Filter by public/private
            if (auth()->check()) {
                $query->where(function ($q) {
                    $q->where('is_public', true)
                      ->orWhere('user_id', auth()->id());
                });
            } else {
                $query->where('is_public', true);
            }

            $notes = $query->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Topic notes retrieved successfully',
                'data' => $notes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve topic notes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created topic.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'subject_id' => 'required|exists:subjects,id',
                'order' => 'integer|min:0'
            ]);

            // Set default order if not provided
            if (!isset($validated['order'])) {
                $lastOrder = Topic::where('subject_id', $validated['subject_id'])
                    ->max('order') ?? 0;
                $validated['order'] = $lastOrder + 1;
            }

            $topic = Topic::create($validated);

            // Increment subject's chapters count
            $subject = Subject::find($validated['subject_id']);
            $subject->increment('chapters_count');

            return response()->json([
                'success' => true,
                'message' => 'Topic created successfully',
                'data' => $topic
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create topic',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified topic.
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $topic = Topic::find($id);

            if (!$topic) {
                return response()->json([
                    'success' => false,
                    'message' => 'Topic not found'
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'order' => 'sometimes|integer|min:0'
            ]);

            $topic->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Topic updated successfully',
                'data' => $topic
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
                'message' => 'Failed to update topic',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified topic.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $topic = Topic::find($id);

            if (!$topic) {
                return response()->json([
                    'success' => false,
                    'message' => 'Topic not found'
                ], 404);
            }

            // Check if topic has notes
            if ($topic->notes()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete topic with existing notes'
                ], 409);
            }

            $subjectId = $topic->subject_id;
            $topic->delete();

            // Decrement subject's chapters count
            Subject::where('id', $subjectId)->decrement('chapters_count');

            return response()->json([
                'success' => true,
                'message' => 'Topic deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete topic',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder topics (bulk update).
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function reorder(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'topics' => 'required|array',
                'topics.*.id' => 'required|exists:topics,id',
                'topics.*.order' => 'required|integer|min:0'
            ]);

            foreach ($validated['topics'] as $item) {
                Topic::where('id', $item['id'])->update(['order' => $item['order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Topics reordered successfully'
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
                'message' => 'Failed to reorder topics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
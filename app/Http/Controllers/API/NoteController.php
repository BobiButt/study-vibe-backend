<?php

// app/Http/Controllers/API/NoteController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Note;
use App\Models\Review;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Display a listing of notes with filters.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Note::query()->with(['user:id,name,profile_photo', 'subject:id,name', 'topic:id,name']);

            // Filter by public/private based on authentication
            if (auth()->check()) {
                // Show public notes + user's private notes
                $query->where(function ($q) {
                    $q->where('is_public', true);
                    if (auth('sanctum')->check()) {
                        $q->orWhere('user_id', auth('sanctum')->id());
                    }
                });
            } else {
                // Show only public notes
                $query->where('is_public', true);
            }

            // Filter by level (school/college/university)
            if ($request->has('level')) {
                $query->where('level', $request->level);
            }

            // Filter by program
            if ($request->has('program_id')) {
                $query->where('program_id', $request->program_id);
            }

            // Filter by subject
            if ($request->has('subject_id')) {
                $query->where('subject_id', $request->subject_id);
            }

            // Filter by topic
            if ($request->has('topic_id')) {
                $query->where('topic_id', $request->topic_id);
            }

            // Filter by AI generated
            if ($request->has('is_ai_generated')) {
                $query->where('is_ai_generated', filter_var($request->is_ai_generated, FILTER_VALIDATE_BOOLEAN));
            }

            // Filter by public/private based on authentication
            if (auth()->check()) {
                // Show public notes + user's private notes
                $query->where(function ($q) {
                    $q->where('is_public', true)
                        ->orWhere('user_id', auth()->id());
                });
            } else {
                // Show only public notes
                $query->where('is_public', true);
            }

            // Search in title and content
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('content', 'LIKE', "%{$search}%")
                        ->orWhere('excerpt', 'LIKE', "%{$search}%");
                });
            }

            // Sort options
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $allowedSorts = ['created_at', 'views_count', 'average_rating', 'title', 'likes_count'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Get top rated if requested
            if ($request->has('top_rated')) {
                $query->where('is_public', true)
                    ->where('average_rating', '>', 0)
                    ->orderBy('average_rating', 'desc')
                    ->orderBy('reviews_count', 'desc');
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $notes = $query->paginate($perPage);

            // Add user-specific data if authenticated
            if (auth()->check()) {
                foreach ($notes as $note) {
                    $note->is_bookmarked = $note->bookmarks()->where('user_id', auth()->id())->exists();
                    $note->user_review = $note->reviews()->where('user_id', auth()->id())->first();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Notes retrieved successfully',
                'data' => $notes,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get top 5 public notes (community featured).
     */
    public function getTopPublicNotes(): JsonResponse
    {
        try {
            $notes = Note::where('is_public', true)
                ->with(['user:id,name,profile_photo', 'subject:id,name'])
                ->withCount('reviews')
                ->orderBy('average_rating', 'desc')
                ->orderBy('reviews_count', 'desc')
                ->orderBy('views_count', 'desc')
                ->limit(5)
                ->get();

            // For notes where user profile is private, use guest image
            foreach ($notes as $note) {
                if (! $note->user->profile_photo) {
                    // Will use guest icon in frontend
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Top public notes retrieved successfully',
                'data' => $notes,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve top notes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified note.
     */
    public function show(int $id): JsonResponse
    {
        \Log::info('========== NOTE VIEW ATTEMPT ==========');
        \Log::info('Note ID: '.$id);
        \Log::info('Auth check: '.(auth()->check() ? 'Authenticated' : 'Not authenticated'));

        // Log the Authorization header
        $token = request()->bearerToken();
        \Log::info('Bearer token present: '.($token ? 'Yes' : 'No'));
        if ($token) {
            \Log::info('Token preview: '.substr($token, 0, 20).'...');
        }

        if (auth()->check()) {
            \Log::info('User ID: '.auth()->id());
            \Log::info('User Email: '.auth()->user()->email);
        }

        try {
            $note = Note::with([
                'user:id,name,profile_photo,university,program',
                'subject:id,name',
                'topic:id,name',
                'program:id,name,full_name',
                'reviews' => function ($query) {
                    $query->with('user:id,name,profile_photo')
                        ->latest()
                        ->limit(10);
                },
            ])->find($id);

            if (! $note) {
                \Log::error('❌ Note not found with ID: '.$id);

                return response()->json([
                    'success' => false,
                    'message' => 'Note not found',
                ], 404);
            }

            \Log::info('📝 Note found:', [
                'id' => $note->id,
                'title' => $note->title,
                'user_id' => $note->user_id,
                'is_public' => $note->is_public,
            ]);

            // Check access (public OR owner)
            $isOwner = auth()->check() && auth()->id() === $note->user_id;
            \Log::info('🔐 Access check:', [
                'is_public' => $note->is_public,
                'is_authenticated' => auth()->check(),
                'request_user_id' => auth()->id(),
                'note_owner_id' => $note->user_id,
                'is_owner' => $isOwner,
                'has_access' => ($note->is_public || $isOwner),
            ]);

            if (! $note->is_public && (! auth()->check() || ! $isOwner)) {
                \Log::warning('⛔ Access denied for note '.$id);

                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this note',
                ], 403);
            }

            // Increment views count only if not owner
            if (! auth()->check() || auth()->id() !== $note->user_id) {
                $note->incrementViews();
                \Log::info('👁️ Views incremented for note '.$id);
            }

            // Get related notes
            $relatedNotes = Note::where('subject_id', $note->subject_id)
                ->where('id', '!=', $note->id)
                ->where('is_public', true)
                ->with('user:id,name,profile_photo')
                ->limit(5)
                ->get();

            // Add user-specific data if authenticated
            if (auth()->check()) {
                $note->is_bookmarked = $note->bookmarks()->where('user_id', auth()->id())->exists();
                $note->user_review = $note->reviews()->where('user_id', auth()->id())->first();
            }

            \Log::info('✅ Note retrieved successfully for ID: '.$id);
            \Log::info('========== END NOTE VIEW ==========');

            return response()->json([
                'success' => true,
                'message' => 'Note retrieved successfully',
                'data' => [
                    'note' => $note,
                    'related_notes' => $relatedNotes,
                ],
            ], 200);

        } catch (\Exception $e) {
            \Log::error('❌ Exception in show method:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve note',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created note.
     */
    public function store(Request $request): JsonResponse
    {
        \Log::info('Note creation attempt:', $request->all());
        try {
            $user = auth()->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'excerpt' => 'nullable|string|max:500',
                'subject_id' => 'nullable|exists:subjects,id',
                'topic_id' => 'nullable|exists:topics,id',
                'program_id' => 'nullable|exists:programs,id',
                'level' => 'required|in:school,college,university',
                'grade_level' => 'nullable|string|max:50',
                'semester' => 'nullable|integer|min:1|max:12',
                'is_ai_generated' => 'boolean',
                'ai_prompt' => 'nullable|string',
                'ai_model' => 'nullable|string',
                'ai_metadata' => 'nullable|json',
                'is_public' => 'boolean',
                'metadata' => 'nullable|json',
            ]);

            // Set default values
            $validated['user_id'] = $user->id;
            $validated['excerpt'] = $validated['excerpt'] ?? substr(strip_tags($validated['content']), 0, 200);

            // Generate excerpt if not provided
            if (empty($validated['excerpt'])) {
                $validated['excerpt'] = substr(strip_tags($validated['content']), 0, 200);
            }

            // Set published_at if public
            if ($validated['is_public'] ?? false) {
                $validated['published_at'] = now();
            }

            $note = Note::create($validated);
            \Log::info('Note created successfully:', ['id' => $note->id]);
            // Update subject's notes count
            if ($note->subject_id) {
                $note->subject->increment('notes_count');
            }

            // Update topic's notes count
            if ($note->topic_id) {
                $note->topic->increment('notes_count');
            }

            return response()->json([
                'success' => true,
                'message' => 'Note created successfully',
                'data' => $note,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', $e->errors());

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Note creation failed:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create note',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified note.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $note = Note::find($id);

            if (! $note) {
                return response()->json([
                    'success' => false,
                    'message' => 'Note not found',
                ], 404);
            }

            // Check ownership
            if ($note->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this note',
                ], 403);
            }

            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'content' => 'sometimes|string',
                'excerpt' => 'nullable|string|max:500',
                'subject_id' => 'nullable|exists:subjects,id',
                'topic_id' => 'nullable|exists:topics,id',
                'program_id' => 'nullable|exists:programs,id',
                'level' => 'sometimes|in:school,college,university',
                'grade_level' => 'nullable|string|max:50',
                'semester' => 'nullable|integer|min:1|max:12',
                'is_public' => 'sometimes|boolean',
                'metadata' => 'nullable|json',
            ]);

            // Update published_at if making public
            if (isset($validated['is_public']) && $validated['is_public'] && ! $note->is_public) {
                $validated['published_at'] = now();
            }

            $note->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Note updated successfully',
                'data' => $note,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update note',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a review.
     *
     * @param  int  $reviewId
     * @return JsonResponse
     */
    public function deleteReview($reviewId)
    {
        try {
            $user = auth()->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $review = Review::find($reviewId);

            if (! $review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found',
                ], 404);
            }

            // Check if the user owns this review
            if ($review->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this review',
                ], 403);
            }

            $noteId = $review->note_id;
            $review->delete();

            // Update the note's average rating
            $note = Note::find($noteId);

            // Force recalculate the rating
            $note->average_rating = $note->reviews()->avg('rating') ?? 0;
            $note->reviews_count = $note->reviews()->count();
            $note->save();

            \Log::info('Review deleted. Note updated:', [
                'note_id' => $noteId,
                'new_average_rating' => $note->average_rating,
                'new_reviews_count' => $note->reviews_count,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully',
                'data' => [
                    'average_rating' => $note->average_rating,
                    'reviews_count' => $note->reviews_count,
                ],
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Failed to delete review:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified note.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $note = Note::find($id);

            if (! $note) {
                return response()->json([
                    'success' => false,
                    'message' => 'Note not found',
                ], 404);
            }

            // Check ownership
            if ($note->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this note',
                ], 403);
            }

            // Update subject's notes count
            if ($note->subject_id) {
                $note->subject->decrement('notes_count');
            }

            // Update topic's notes count
            if ($note->topic_id) {
                $note->topic->decrement('notes_count');
            }

            $note->delete();

            return response()->json([
                'success' => true,
                'message' => 'Note deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete note',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle note visibility (public/private).
     */
    public function toggleVisibility(int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $note = Note::find($id);

            if (! $note) {
                return response()->json([
                    'success' => false,
                    'message' => 'Note not found',
                ], 404);
            }

            // Check ownership
            if ($note->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to modify this note',
                ], 403);
            }

            $note->is_public = ! $note->is_public;

            if ($note->is_public) {
                $note->published_at = now();
            }

            $note->save();

            return response()->json([
                'success' => true,
                'message' => $note->is_public ? 'Note is now public' : 'Note is now private',
                'data' => ['is_public' => $note->is_public],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle visibility',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add a review/rating to a note.
     */
    public function addReview(Request $request, int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $note = Note::find($id);

            if (! $note) {
                return response()->json([
                    'success' => false,
                    'message' => 'Note not found',
                ], 404);
            }

            // Can't review your own note
            if ($note->user_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot review your own note',
                ], 422);
            }

            $validated = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
                'is_helpful' => 'boolean',
            ]);

            // Check if user already reviewed
            $existingReview = Review::where('user_id', $user->id)
                ->where('note_id', $id)
                ->first();

            if ($existingReview) {
                // Update existing review
                $existingReview->update($validated);
                $review = $existingReview;
            } else {
                // Create new review
                $review = Review::create([
                    'user_id' => $user->id,
                    'note_id' => $id,
                    'rating' => $validated['rating'],
                    'comment' => $validated['comment'] ?? null,
                    'is_helpful' => $validated['is_helpful'] ?? false,
                ]);
            }

            // Update note's average rating (handled by Review model boot method)

            return response()->json([
                'success' => true,
                'message' => 'Review added successfully',
                'data' => $review,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add review',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle bookmark for a note.
     */
    public function toggleBookmark(int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $note = Note::find($id);

            if (! $note) {
                return response()->json([
                    'success' => false,
                    'message' => 'Note not found',
                ], 404);
            }

            $bookmark = Bookmark::where('user_id', $user->id)
                ->where('note_id', $id)
                ->first();

            if ($bookmark) {
                $bookmark->delete();
                $message = 'Bookmark removed';
                $isBookmarked = false;
            } else {
                Bookmark::create([
                    'user_id' => $user->id,
                    'note_id' => $id,
                ]);
                $message = 'Bookmark added';
                $isBookmarked = true;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => ['is_bookmarked' => $isBookmarked],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle bookmark',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's notes (private + public).
     */
    public function myNotes(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $query = Note::where('user_id', $user->id)
                ->with(['subject:id,name', 'topic:id,name']);

            // Filter by public/private
            if ($request->has('is_public')) {
                $query->where('is_public', filter_var($request->is_public, FILTER_VALIDATE_BOOLEAN));
            }

            // Filter by AI generated
            if ($request->has('is_ai_generated')) {
                $query->where('is_ai_generated', filter_var($request->is_ai_generated, FILTER_VALIDATE_BOOLEAN));
            }

            $notes = $query->orderBy('created_at', 'desc')->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Your notes retrieved successfully',
                'data' => $notes,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve your notes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's bookmarked notes.
     */
    public function bookmarkedNotes(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $notes = $user->bookmarkedNotes()
                ->with(['user:id,name,profile_photo', 'subject:id,name'])
                ->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Bookmarked notes retrieved successfully',
                'data' => $notes,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve bookmarked notes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

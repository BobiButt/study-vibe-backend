<?php
// app/Http/Controllers/API/AINoteController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AINoteController extends Controller
{
    /**
     * Save AI-generated notes from frontend.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'subject_id' => 'nullable|exists:subjects,id',
                'program_id' => 'nullable|exists:programs,id',
                'level' => 'required|in:school,college,university',
                'semester' => 'nullable|integer',
                'ai_prompt' => 'nullable|string',
                'ai_model' => 'nullable|string',
                'is_public' => 'boolean'
            ]);

            $validated['user_id'] = auth()->id();
            $validated['is_ai_generated'] = true;
            $validated['excerpt'] = substr(strip_tags($validated['content']), 0, 200);

            $note = Note::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'AI note saved successfully',
                'data' => $note
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save AI note',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available AI models (for frontend selection).
     */
    public function getModels(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                ['id' => 'gpt-5.1', 'name' => 'GPT-5.1', 'best_for' => 'General topics'],
                ['id' => 'deepseek-r1', 'name' => 'DeepSeek R1', 'best_for' => 'Math & Coding'],
                ['id' => 'claude-4.5', 'name' => 'Claude 4.5', 'best_for' => 'Detailed explanations']
            ]
        ]);
    }
}
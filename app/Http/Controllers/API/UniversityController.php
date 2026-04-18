<?php
// app/Http/Controllers/API/UniversityController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UniversityController extends Controller
{
    /**
     * Get all university programs.
     */
    public function getPrograms(): JsonResponse
    {
        try {
            $programs = Program::where('level', 'university')
                ->withCount('subjects')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $programs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch programs'
            ], 500);
        }
    }

    /**
     * Get subjects by program and semester.
     */
    public function getSubjects(Request $request, int $programId): JsonResponse
    {
        try {
            $query = Subject::where('program_id', $programId)
                ->where('level', 'university');

            if ($request->has('semester')) {
                $query->where('semester', $request->semester);
            }

            $subjects = $query->orderBy('semester')->get();

            return response()->json([
                'success' => true,
                'data' => $subjects
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subjects'
            ], 500);
        }
    }

    /**
     * Get university notes.
     */
    public function getNotes(Request $request): JsonResponse
    {
        try {
            $query = Note::where('level', 'university')
                ->with(['user', 'subject', 'program']);

            if ($request->has('program_id')) {
                $query->where('program_id', $request->program_id);
            }

            if ($request->has('subject_id')) {
                $query->where('subject_id', $request->subject_id);
            }

            if ($request->has('semester')) {
                $query->where('semester', $request->semester);
            }

            if ($request->has('is_ai_generated')) {
                $query->where('is_ai_generated', $request->boolean('is_ai_generated'));
            }

            $notes = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $notes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch university notes'
            ], 500);
        }
    }
}
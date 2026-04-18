<?php
// app/Http/Controllers/API/SchoolController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SchoolController extends Controller
{
    /**
     * Get all school subjects with optional grade filter.
     */
    public function getSubjects(Request $request): JsonResponse
    {
        try {
            $query = Subject::where('level', 'school')
                ->withCount('notes');

            if ($request->has('grade')) {
                $query->where('grade_level', $request->grade);
            }

            $subjects = $query->get();

            return response()->json([
                'success' => true,
                'data' => $subjects
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch school subjects'
            ], 500);
        }
    }

    /**
     * Get available grade levels (6-10).
     */
    public function getGradeLevels(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ['6th Grade', '7th Grade', '8th Grade', '9th Grade', '10th Grade']
        ]);
    }

    /**
     * Get notes for school level.
     */
    public function getNotes(Request $request): JsonResponse
    {
        try {
            $query = Note::where('level', 'school')
                ->with(['user', 'subject']);

            if ($request->has('grade')) {
                $query->where('grade_level', $request->grade);
            }

            if ($request->has('subject_id')) {
                $query->where('subject_id', $request->subject_id);
            }

            $notes = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $notes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch school notes'
            ], 500);
        }
    }
}
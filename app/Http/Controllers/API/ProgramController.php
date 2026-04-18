<?php
// app/Http/Controllers/API/ProgramController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProgramController extends Controller
{
    /**
     * Display a listing of all programs.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get query parameters for filtering
            $level = $request->query('level'); // school, college, university
            $isActive = $request->query('is_active', true);

            // Build query
            $query = Program::query();
            
            // Filter by level if provided
            if ($level) {
                $query->where('level', $level);
            }

            // Filter by active status
            if ($isActive !== null) {
                $query->where('is_active', $isActive === 'true' || $isActive === true);
            }

            // Get programs with subject counts
            $programs = $query->withCount('subjects')->get();

            // Transform data for frontend
            $formattedPrograms = $programs->map(function ($program) {
                return [
                    'id' => $program->id,
                    'name' => $program->name,
                    'full_name' => $program->full_name,
                    'icon' => $program->icon,
                    'color' => $program->color,
                    'description' => $program->description,
                    'duration' => $program->duration,
                    'semesters' => $program->semesters,
                    'level' => $program->level,
                    'is_active' => $program->is_active,
                    'subjects_count' => $program->subjects_count,
                    'created_at' => $program->created_at,
                    'updated_at' => $program->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Programs retrieved successfully',
                'data' => $formattedPrograms
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve programs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get programs for university level.
     * 
     * @return JsonResponse
     */
    public function getUniversityPrograms(): JsonResponse
    {
        try {
            $programs = Program::where('level', 'university')
                ->where('is_active', true)
                ->withCount('subjects')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'University programs retrieved successfully',
                'data' => $programs
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve university programs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get programs for college level (FSc, I.COM, etc.).
     * 
     * @return JsonResponse
     */
    public function getCollegePrograms(): JsonResponse
    {
        try {
            // For college, we might have streams like Pre-Medical, Pre-Engineering, etc.
            $programs = Program::where('level', 'college')
                ->where('is_active', true)
                ->withCount('subjects')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'College programs retrieved successfully',
                'data' => $programs
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve college programs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get programs for school level (grades 6-10).
     * 
     * @return JsonResponse
     */
    public function getSchoolPrograms(): JsonResponse
    {
        try {
            // For school, programs might be grade-based or just general
            $programs = Program::where('level', 'school')
                ->where('is_active', true)
                ->withCount('subjects')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'School programs retrieved successfully',
                'data' => $programs
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve school programs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified program with its subjects.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $program = Program::with(['subjects' => function ($query) {
                $query->where('is_active', true)
                      ->orderBy('semester')
                      ->orderBy('name');
            }])->find($id);

            if (!$program) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program not found'
                ], 404);
            }

            // Format subjects by semester for easier frontend consumption
            $subjectsBySemester = [];
            if ($program->level === 'university') {
                foreach ($program->subjects as $subject) {
                    $semester = $subject->semester ?? 1;
                    if (!isset($subjectsBySemester[$semester])) {
                        $subjectsBySemester[$semester] = [];
                    }
                    $subjectsBySemester[$semester][] = [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'icon' => $subject->icon,
                        'color' => $subject->color,
                        'description' => $subject->description,
                        'chapters_count' => $subject->chapters_count,
                        'notes_count' => $subject->notes_count,
                        'progress' => 0, // This will be calculated from user's study progress
                    ];
                }
            }

            $responseData = [
                'id' => $program->id,
                'name' => $program->name,
                'full_name' => $program->full_name,
                'icon' => $program->icon,
                'color' => $program->color,
                'description' => $program->description,
                'duration' => $program->duration,
                'semesters' => $program->semesters,
                'level' => $program->level,
                'is_active' => $program->is_active,
                'created_at' => $program->created_at,
                'updated_at' => $program->updated_at,
            ];

            // Add subjects in appropriate format
            if ($program->level === 'university') {
                $responseData['subjects_by_semester'] = $subjectsBySemester;
            } else {
                $responseData['subjects'] = $program->subjects;
            }

            return response()->json([
                'success' => true,
                'message' => 'Program retrieved successfully',
                'data' => $responseData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve program',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created program.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'full_name' => 'required|string|max:255',
                'icon' => 'nullable|string|max:50',
                'color' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'duration' => 'required|string|max:50',
                'semesters' => 'required|integer|min:1|max:12',
                'level' => 'required|in:school,college,university',
                'is_active' => 'boolean'
            ]);

            $program = Program::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Program created successfully',
                'data' => $program
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
                'message' => 'Failed to create program',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified program.
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $program = Program::find($id);

            if (!$program) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program not found'
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'full_name' => 'sometimes|string|max:255',
                'icon' => 'nullable|string|max:50',
                'color' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'duration' => 'sometimes|string|max:50',
                'semesters' => 'sometimes|integer|min:1|max:12',
                'level' => 'sometimes|in:school,college,university',
                'is_active' => 'sometimes|boolean'
            ]);

            $program->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Program updated successfully',
                'data' => $program
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
                'message' => 'Failed to update program',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified program.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $program = Program::find($id);

            if (!$program) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program not found'
                ], 404);
            }

            // Check if program has subjects
            if ($program->subjects()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete program with existing subjects'
                ], 409);
            }

            $program->delete();

            return response()->json([
                'success' => true,
                'message' => 'Program deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete program',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subjects for a specific program.
     * 
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function getSubjects(int $id, Request $request): JsonResponse
    {
        try {
            $program = Program::find($id);

            if (!$program) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program not found'
                ], 404);
            }

            $semester = $request->query('semester');
            $gradeLevel = $request->query('grade_level');

            $query = $program->subjects()->where('is_active', true);

            // Filter by semester for university
            if ($semester && $program->level === 'university') {
                $query->where('semester', $semester);
            }

            // Filter by grade level for school
            if ($gradeLevel && $program->level === 'school') {
                $query->where('grade_level', $gradeLevel);
            }

            $subjects = $query->withCount('topics')->get();

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
}
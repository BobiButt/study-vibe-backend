<?php
// app/Http/Controllers/API/CollegeController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CollegeController extends Controller
{
    /**
     * Get available streams.
     */
    public function getStreams(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                ['id' => 'science', 'name' => 'Science (Pre-Medical/Pre-Engineering)'],
                ['id' => 'commerce', 'name' => 'Commerce'],
                ['id' => 'arts', 'name' => 'Arts/Humanities']
            ]
        ]);
    }

    /**
     * Get subjects by stream.
     */
    public function getSubjectsByStream(Request $request, string $stream): JsonResponse
    {
        try {
            // You can customize this based on your database structure
            $subjects = Subject::where('level', 'college')
                ->where('program_id', function($query) use ($stream) {
                    $query->select('id')
                        ->from('programs')
                        ->where('name', 'LIKE', "%$stream%");
                })
                ->get();

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
     * Get college notes.
     */
    public function getNotes(Request $request): JsonResponse
    {
        try {
            $query = Note::where('level', 'college')
                ->with(['user', 'subject']);

            if ($request->has('stream')) {
                $query->whereHas('program', function($q) use ($request) {
                    $q->where('name', 'LIKE', "%{$request->stream}%");
                });
            }

            if ($request->has('year')) {
                $query->where('semester', $request->year === '11th' ? 1 : 2);
            }

            $notes = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $notes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch college notes'
            ], 500);
        }
    }
}
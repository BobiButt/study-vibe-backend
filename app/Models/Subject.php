<?php
// app/Models/Subject.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'color',
        'description',
        'program_id',
        'semester',
        'grade_level',
        'level',
        'chapters_count',
        'notes_count',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'semester' => 'integer',
        'chapters_count' => 'integer',
        'notes_count' => 'integer'
    ];

    /**
     * Get the program that owns the subject.
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the topics for this subject.
     */
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    /**
     * Get the notes for this subject.
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get the study progress for this subject.
     */
    public function studyProgress()
    {
        return $this->hasMany(StudyProgress::class);
    }

    /**
     * Scope a query to only include active subjects.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include subjects by level.
     */
    public function scopeOfLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope a query to only include subjects by grade.
     */
    public function scopeOfGrade($query, $grade)
    {
        return $query->where('grade_level', $grade);
    }

    /**
     * Scope a query to only include subjects by semester.
     */
    public function scopeOfSemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }
}
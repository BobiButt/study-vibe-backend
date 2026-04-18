<?php
// app/Models/Topic.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'subject_id',
        'order',
        'is_completed',
        'notes_count'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'order' => 'integer',
        'notes_count' => 'integer'
    ];

    /**
     * Get the subject that owns the topic.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the notes for this topic.
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get the study progress for this topic.
     */
    public function studyProgress()
    {
        return $this->hasMany(StudyProgress::class);
    }
}
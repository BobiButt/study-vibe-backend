<?php
// app/Models/StudyProgress.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject_id',
        'topic_id',
        'note_id',
        'progress_percentage',
        'last_studied_at',
        'study_time_minutes'
    ];

    protected $casts = [
        'progress_percentage' => 'integer',
        'last_studied_at' => 'datetime',
        'study_time_minutes' => 'integer'
    ];

    /**
     * Get the user that owns the progress.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject associated with this progress.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the topic associated with this progress.
     */
    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * Get the note associated with this progress.
     */
    public function note()
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Update last studied timestamp.
     */
    public function updateLastStudied()
    {
        $this->last_studied_at = now();
        $this->save();
    }

    /**
     * Add study time.
     */
    public function addStudyTime($minutes)
    {
        $this->study_time_minutes += $minutes;
        $this->save();
    }
}
<?php
// app/Models/Note.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'excerpt',
        'subject_id',
        'topic_id',
        'program_id',
        'level',
        'grade_level',
        'semester',
        'is_ai_generated',
        'ai_prompt',
        'ai_model',
        'ai_metadata',
        'is_public',
        'published_at',
        'views_count',
        'likes_count',
        'downloads_count',
        'average_rating',
        'reviews_count',
        'metadata',
        'is_featured'
    ];

    protected $casts = [
        'is_ai_generated' => 'boolean',
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'ai_metadata' => 'json',
        'metadata' => 'json',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'downloads_count' => 'integer',
        'average_rating' => 'float',
        'reviews_count' => 'integer',
        'semester' => 'integer'
    ];

    /**
     * Get the user that owns the note.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject that owns the note.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the topic that owns the note.
     */
    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * Get the program that owns the note.
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the reviews for this note.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the bookmarks for this note.
     */
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get the users who bookmarked this note.
     */
    public function bookmarkedBy()
    {
        return $this->belongsToMany(User::class, 'bookmarks')->withTimestamps();
    }

    /**
     * Get the study progress for this note.
     */
    public function studyProgress()
    {
        return $this->hasMany(StudyProgress::class);
    }

    /**
     * Scope a query to only include public notes.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to only include private notes.
     */
    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    /**
     * Scope a query to only include AI generated notes.
     */
    public function scopeAiGenerated($query)
    {
        return $query->where('is_ai_generated', true);
    }

    /**
     * Scope a query to only include notes by level.
     */
    public function scopeOfLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope a query to get top rated notes.
     */
    public function scopeTopRated($query, $limit = 5)
    {
        return $query->public()
            ->where('average_rating', '>', 0)
            ->orderBy('average_rating', 'desc')
            ->orderBy('reviews_count', 'desc')
            ->limit($limit);
    }

    /**
     * Scope a query to get most viewed notes.
     */
    public function scopeMostViewed($query, $limit = 5)
    {
        return $query->public()
            ->orderBy('views_count', 'desc')
            ->limit($limit);
    }

    /**
     * Increment views count.
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Update average rating when a new review is added.
     */
   public function updateRating()
{
    $this->average_rating = $this->reviews()->avg('rating') ?? 0;
    $this->reviews_count = $this->reviews()->count();
    $this->saveQuietly(); // Use saveQuietly to avoid triggering events
    
    \Log::info('Note rating updated:', [
        'note_id' => $this->id,
        'average_rating' => $this->average_rating,
        'reviews_count' => $this->reviews_count
    ]);
}
}
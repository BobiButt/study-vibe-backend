<?php
// app/Models/Review.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'note_id',
        'rating',
        'comment',
        'is_helpful'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_helpful' => 'boolean'
    ];

    /**
     * Get the user that wrote the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the note that was reviewed.
     */
    public function note()
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($review) {
            $review->note->updateRating();
        });

        static::updated(function ($review) {
            $review->note->updateRating();
        });

        static::deleted(function ($review) {
            $review->note->updateRating();
        });
    }
}
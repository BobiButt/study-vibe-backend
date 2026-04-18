<?php

namespace App\Models;

use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        // New fields for education platform
        'username',
        'profile_photo',
        'cloudinary_public_id',
        'bio',
        'university',
        'program',
        'semester',
        'grade_level',
        'education_level',
        'study_streak',
        'last_active_at',
        'preferences',
        'google_id',
         'google_avatar_url',
         'google_token',
         'google_refresh_token',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        // New casts
        'preferences' => 'json',
        'semester' => 'integer',
        'study_streak' => 'integer',
        'last_active_at' => 'datetime'
    ];

    /**
     * Override the default email verification notification
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    /**
     * =============================================
     * RELATIONSHIPS
     * =============================================
     */

    /**
     * Get the notes for the user.
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get the reviews for the user.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the bookmarks for the user.
     */
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get the bookmarked notes.
     */
    public function bookmarkedNotes()
    {
        return $this->belongsToMany(Note::class, 'bookmarks')->withTimestamps();
    }

    /**
     * Get the study progress for the user.
     */
    public function studyProgress()
    {
        return $this->hasMany(StudyProgress::class);
    }

    /**
     * =============================================
     * SCOPES
     * =============================================
     */

    /**
     * Scope a query to only include users by education level.
     */
    public function scopeOfEducationLevel($query, $level)
    {
        return $query->where('education_level', $level);
    }

    /**
     * Scope a query to only include active users (last 7 days).
     */
    public function scopeActive($query)
    {
        return $query->where('last_active_at', '>=', now()->subDays(7));
    }

    /**
     * =============================================
     * ACCESSORS & MUTATORS
     * =============================================
     */

    /**
     * Get the user's profile photo URL.
     */
   // You already have this in your User model:
/**
 * Get the user's profile photo URL.
 * Priority: Custom uploaded > Google avatar > Default avatar
 */
public function getProfilePhotoUrlAttribute()
{
    // Priority 1: User uploaded custom photo (from Cloudinary)
    if ($this->profile_photo) {
        return $this->profile_photo;
    }
    
    // Priority 2: Google avatar (if logged in with Google)
    if ($this->google_avatar_url) {
        return $this->google_avatar_url;
    }
    
    // Priority 3: Default avatar based on name
    return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
}

    /**
     * Get the user's display name (username or name).
     */
    public function getDisplayNameAttribute()
    {
        return $this->username ?? $this->name;
    }

    /**
     * =============================================
     * HELPER METHODS
     * =============================================
     */

    /**
     * Update last active timestamp.
     */
    public function updateLastActive()
    {
        $this->last_active_at = now();
        $this->save();
    }

    /**
     * Increment study streak.
     */
    public function incrementStudyStreak()
    {
        $this->study_streak++;
        $this->save();
    }

    /**
     * Reset study streak.
     */
    public function resetStudyStreak()
    {
        $this->study_streak = 0;
        $this->save();
    }

    /**
     * Check if user has studied today.
     */
    public function hasStudiedToday()
    {
        return $this->studyProgress()
            ->whereDate('last_studied_at', today())
            ->exists();
    }

    /**
     * Get user's public notes.
     */
    public function publicNotes()
    {
        return $this->notes()->where('is_public', true);
    }

    /**
     * Get user's private notes.
     */
    public function privateNotes()
    {
        return $this->notes()->where('is_public', false);
    }

    /**
     * Get user's AI generated notes.
     */
    public function aiNotes()
    {
        return $this->notes()->where('is_ai_generated', true);
    }

    /**
     * Get total study time in hours.
     */
    public function getTotalStudyHoursAttribute()
    {
        return round($this->studyProgress()->sum('study_time_minutes') / 60, 1);
    }

    /**
     * Get completion rate (percentage of topics completed).
     */
    public function getCompletionRateAttribute()
    {
        $totalTopics = StudyProgress::where('user_id', $this->id)->count();
        if ($totalTopics === 0) return 0;
        
        $completedTopics = StudyProgress::where('user_id', $this->id)
            ->where('progress_percentage', 100)
            ->count();
        
        return round(($completedTopics / $totalTopics) * 100);
    }
    
}
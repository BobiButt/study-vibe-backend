<?php
// app/Models/Program.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'full_name',
        'icon',
        'color',
        'description',
        'duration',
        'semesters',
        'level',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'semesters' => 'integer'
    ];

    /**
     * Get the subjects for this program.
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Get the notes for this program.
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Scope a query to only include active programs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include programs by level.
     */
    public function scopeOfLevel($query, $level)
    {
        return $query->where('level', $level);
    }
}
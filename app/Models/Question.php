<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Question extends Model
{
    use HasFactory;

    protected $table = 'questions';

    protected $fillable = [
        'academic_level',
        'chapter', 
        'difficulty',
        'question_image',
        'answer_image',
        'tip_easy',      // Changed from tip_easy_image
        'tip_intermediate',  // Changed from tip_intermediate_image
        'tip_advanced',     // Changed from tip_advanced_image
        'uploaded_by',
        'upload_date',
        'user_id',
    ];

    protected $casts = [
        'upload_date' => 'datetime',
    ];

    // Accessor for question image URL
    public function getQuestionImageUrlAttribute()
    {
        return $this->question_image ? Storage::url($this->question_image) : null;
    }

    // Updated accessors for tip images (using new column names)
    public function getTipEasyImageUrlAttribute()
    {
        return $this->tip_easy ? Storage::url($this->tip_easy) : null;
    }

    public function getTipIntermediateImageUrlAttribute()
    {
        return $this->tip_intermediate ? Storage::url($this->tip_intermediate) : null;
    }

    public function getTipAdvancedImageUrlAttribute()
    {
        return $this->tip_advanced ? Storage::url($this->tip_advanced) : null;
    }

    // Get appropriate tip image based on difficulty
    public function getTipImageByDifficulty($userLevel = 'intermediate')
    {
        switch (strtolower($userLevel)) {
            case 'easy':
                return $this->tip_easy_image_url;
            case 'advanced':
            case 'expert':
                return $this->tip_advanced_image_url;
            default:
                return $this->tip_intermediate_image_url;
        }
    }

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    // All your existing scope methods...
    public function scopeByLevel($query, $level)
    {
        return $query->where('academic_level', $level);
    }

    public function scopeByChapter($query, $chapter)
    {
        return $query->where('chapter', $chapter);
    }

    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    public function scopeByUploader($query, $uploader)
    {
        return $query->where('uploaded_by', $uploader);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('upload_date', '>=', Carbon::now()->subDays($days));
    }

    public function isCorrectAnswer($selectedAnswer)
    {
        return strtoupper($selectedAnswer) === strtoupper($this->answer_image);
    }

    public function getFormattedUploadDateAttribute()
    {
        return $this->upload_date ? $this->upload_date->format('M d, Y') : null;
    }

    public function getTimeSinceUploadAttribute()
    {
        return $this->upload_date ? $this->upload_date->diffForHumans() : null;
    }

    public function scopeRandom($query, $limit = 10)
    {
        return $query->inRandomOrder()->limit($limit);
    }

    public function scopeByDifficulties($query, array $difficulties)
    {
        return $query->whereIn('difficulty', $difficulties);
    }
}
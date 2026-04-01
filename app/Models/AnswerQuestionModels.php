<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnswerQuestionModels extends Model
{
    use HasFactory;

    protected $table = 'user_answers';

    protected $fillable = [
        'user_id',
        'academic_level',
        'chapter',
        'difficulty',
        'question_pool',
        'answers',
        'current_index',
        'status',
        'score',
    ];

    protected $casts = [
        'question_pool' => 'array',
        'answers' => 'array',
    ];

    public function getCompletedAttribute()
    {
        return $this->status === 'finished';
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in-progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'finished');
    }
}


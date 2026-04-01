<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'teacher_id',
        'details',
        'max_size',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // Add this people relationship:
    public function people()
    {
        return $this->belongsToMany(User::class, 'enrollments', 'classroom_id', 'user_id');
    }

    public function currentEnrollmentCount()
    {
        return $this->enrollments()->count();
    }

    public function isFull()
    {
        return $this->max_size !== null && $this->currentEnrollmentCount() >= $this->max_size;
    }
}

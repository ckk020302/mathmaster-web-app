<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyRoom extends Model
{
    protected $fillable = [
        'name',
        'member_limit',
        'description',
        'is_private',
        'room_code',
        'members',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'members' => 'array',
    ];
}

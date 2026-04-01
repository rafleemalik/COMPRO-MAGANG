<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentFeedbackLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'mentor_user_id',
        'feedback',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function mentorUser()
    {
        return $this->belongsTo(User::class, 'mentor_user_id');
    }
}


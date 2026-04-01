<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AssignmentFeedbackLog;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'assignment_type',
        'description',
        'deadline',
        'presentation_date',
        'file_path',
        'submission_file_path',
        'grade',
        'submitted_at',
        'feedback',
        'is_revision',
        'online_text',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'deadline' => 'date',
        'presentation_date' => 'date',
        'is_revision' => 'integer',
    ];

    /**
     * Get the user that owns the assignment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function feedbackLogs()
    {
        return $this->hasMany(AssignmentFeedbackLog::class);
    }
}

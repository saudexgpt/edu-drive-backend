<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewAttempt extends Model
{
    use HasFactory;
    public function quizAnswers()
    {
        return $this->hasMany(InterviewAnswer::class, 'interview_attempt_id', 'id');
    }
    public function quizCompilation()
    {
        return $this->belongsTo(InterviewCompilation::class, 'interview_compilation_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewCompilation extends Model
{
    use HasFactory;
    public function quizzes()
    {
        return $this->hasMany(Interview::class, 'interview_compilation_id', 'id');
    }
    public function quizAttempts()
    {
        return $this->hasMany(InterviewAttempt::class, 'interview_compilation_id', 'id');
    }
    public function quizAnswers()
    {

        return $this->hasManyThrough(InterviewAnswer::class, InterviewAttempt::class);
    }
}

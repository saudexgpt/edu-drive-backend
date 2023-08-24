<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewQuestion extends Model
{
    use HasFactory;
    public function quizzes()
    {
        return $this->hasMany(Interview::class, 'interview_question_id', 'id');
    }
}

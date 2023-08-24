<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    use HasFactory;
    public function question()
    {
        return $this->belongsTo(InterviewQuestion::class, 'interview_question_id', 'id');
    }
}

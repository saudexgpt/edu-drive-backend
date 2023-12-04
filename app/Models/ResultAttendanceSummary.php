<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultAttendanceSummary extends Model
{
    use HasFactory;
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function classTeacher()
    {
        return $this->belongsTo(ClassTeacher::class);
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function sess()
    {
        return $this->belongsTo(SSession::class);
    }
    public function term()
    {
        return $this->belongsTo(Term::class);
    }
}

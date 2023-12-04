<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('result_attendance_summaries', function (Blueprint $table) {
            $table->id();
            $table->integer('school_id');
            $table->integer('class_teacher_id');
            $table->integer('student_id');
            $table->integer('sess_id');
            $table->integer('term_id');
            $table->integer('opened');
            $table->integer('present');
            $table->integer('absent');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_attendance_summaries');
    }
};

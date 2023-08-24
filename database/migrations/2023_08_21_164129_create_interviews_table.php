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
        Schema::create('interview_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('school_id');
            $table->longText('question');
            $table->string('optA')->nullable();
            $table->string('optB')->nullable();
            $table->string('optC')->nullable();
            $table->string('optD')->nullable();
            $table->string('answer', 30);
            $table->enum('question_type', ['multi_choice', 'true_false'])->nullable()->default('multi_choice');
            $table->integer('point')->nullable();
            $table->timestamps();
        });
        Schema::create('interview_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('interview_attempt_id')->nullable();
            $table->integer('interview_question_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('candidate_no', 50)->nullable();
            $table->string('candidate_answer')->nullable();
            $table->integer('point_earned')->nullable();
            $table->timestamps();
        });
        Schema::create('interview_attempts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('interview_compilation_id');
            $table->enum('has_submitted', ['no', 'yes'])->default('no');
            $table->integer('remaining_time');
            $table->integer('score_limit')->nullable();
            $table->float('candidate_point')->nullable();
            $table->float('percent_score')->nullable();
            $table->timestamps();
        });
        Schema::create('interview_compilations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('school_id');
            $table->enum('question_type', ['objective', 'theory'])->default('objective');
            $table->text('instructions')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('point');
            $table->enum('status', ['inactive', 'active'])->default('inactive');
            $table->integer('exam_code');
            $table->timestamps();
        });
        Schema::create('interviews', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('school_id');
            $table->integer('interview_question_id')->nullable();
            $table->integer('interview_compilation_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_questions');
        Schema::dropIfExists('interview_answers');
        Schema::dropIfExists('interview_attempts');
        Schema::dropIfExists('interview_compilations');
        Schema::dropIfExists('interviews');
    }
};

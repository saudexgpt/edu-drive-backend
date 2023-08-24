<?php

namespace App\Http\Controllers\Interview;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InterviewQuestion as Question;
use App\Models\Interview as Quiz;
use App\Models\InterviewAnswer as QuizAnswer;
use App\Models\InterviewAttempt as QuizAttempt;
use App\Models\InterviewCompilation as QuizCompilation;

class InterviewsController extends Controller
{

    public function staffInterviews(Request $request)
    {
        $exam_id = $request->exam_id;
        $exam_code = $request->exam_code;
        $quiz_compilation = QuizCompilation::with([
            'quizzes.question',
        ])
            ->where('status', 'active')
            ->where('exam_code', $exam_code)->find($exam_id);
        if ($quiz_compilation) {
            return response()->json(compact('quiz_compilation'), 200);
        }

        return response()->json(['message' => 'Test Not Available'], 404);
    }
    public function fetchQuestionBank(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $questions = Question::where('school_id', $school_id)->orderBy('id', 'DESC')->paginate($request->limit);
        return response()->json(compact('questions'), 200);
    }
    public function fetchCompiledQuizzes(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $quiz_compilations = QuizCompilation::with('quizzes', 'quizAttempts.quizAnswers.question', 'quizAttempts.user')->where('school_id', $school_id)->orderBy('id', 'DESC')->get();
        return response()->json(compact('quiz_compilations'), 200);
    }
    public function attemptQuiz(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $user = $this->getUser();
        $quiz_compilation_id = $request->quiz_compilation_id;
        $quiz_compilation = QuizCompilation::find($quiz_compilation_id);
        $quiz_attempt = QuizAttempt::with(['quizAnswers' => function ($query) use ($user) {
            return $query->where('user_id', $user->id)->with([
                'question'
            ]);
        }])->where(['user_id' => $user->id, 'interview_compilation_id' => $quiz_compilation_id])->first();

        if (!$quiz_attempt) {
            $quiz_attempt = new QuizAttempt();
            $quiz_attempt->user_id = $user->id;
            $quiz_attempt->interview_compilation_id = $quiz_compilation_id;
            $quiz_attempt->has_submitted = 'no';
            $quiz_attempt->remaining_time = $request->remaining_time;

            $quiz_attempt->save();

            $quizzes = Quiz::where(['school_id' => $school_id, 'interview_compilation_id' => $quiz_compilation_id])->inRandomOrder()->get();

            $answers = [];
            foreach ($quizzes as $quiz) {
                $quiz_answer = new QuizAnswer();
                $quiz_answer->interview_question_id = $quiz->interview_question_id;
                $quiz_answer->interview_attempt_id = $quiz_attempt->id;
                $quiz_answer->user_id = $user->id;
                $quiz_answer->candidate_no = $user->username;

                $quiz_answer->save();

                $answers[] = QuizAnswer::with(['question'])->find($quiz_answer->id);
            }
        } else {
            $answers = $quiz_attempt->quizAnswers;
        }

        return response()->json(compact('quiz_attempt', 'answers'), 200);
    }
    public function updateRemainingTime(Request $request)
    {
        $quiz_attempt = QuizAttempt::find($request->id);
        $quiz_attempt->remaining_time = $request->rt;
        $quiz_attempt->save();
    }

    private function percentScore($score, $total)
    {
        $percent_score = (int) (($score / $total) * 100);
        return $percent_score;
    }
    private function convertToPointLimit($limit, $percent_score)
    {
        $point = ($limit * $percent_score) / 100;
        return $point;
    }
    private function markExam($student_answer, $correct_answer)
    {
        $point = 0;
        if ($student_answer == $correct_answer) {
            $point = 1;
        }

        return $point;
    }
    public function submitQuizAnswers(Request $request)
    {
        $answers = $request->toArray();
        $student_score = 0;
        $total_score = 0;

        foreach ($answers as $answer) {
            $quiz_attempt_id = $answer['interview_attempt_id'];
            $quiz_answer_id = $answer['id'];
            $candidate_answer = $answer['candidate_answer'];
            $correct_answer = $answer['question']['answer'];
            $point_earned = $this->markExam($candidate_answer, $correct_answer);
            $student_score += $point_earned;
            $total_score++;

            $quiz_answer = QuizAnswer::find($quiz_answer_id);
            $quiz_answer->candidate_answer = $candidate_answer;
            $quiz_answer->point_earned = $point_earned;
            $quiz_answer->save();
        }
        $quiz_attempt = QuizAttempt::find($quiz_attempt_id);

        $compiled_quiz = QuizCompilation::find($quiz_attempt->interview_compilation_id);
        $limit = $compiled_quiz->point;
        $percent_score = $this->percentScore($student_score, $total_score);
        $candidate_point = $this->convertToPointLimit($limit, $percent_score);

        $quiz_attempt->has_submitted = 'yes';
        $quiz_attempt->remaining_time = 0;
        $quiz_attempt->percent_score = $percent_score;
        $quiz_attempt->candidate_point = $candidate_point;
        $quiz_attempt->score_limit = $limit;
        $quiz_attempt->save();
        return response()->json(compact('percent_score', 'candidate_point', 'limit'), 200);
    }
    public function storeQuestion(Request $request)
    {
        $question_type = $request->question_type;

        $question = new Question();
        $question->school_id = $this->getSchool()->id;
        $question->question = $request->question;
        $question->optA = $request->optA;
        $question->optB = $request->optB;
        $question->optC = $request->optC;
        $question->optD = $request->optD;
        $question->answer = $request->answer;
        $question->question_type = $question_type;
        $question->point = $request->point;
        $question->save();



        return response()->json(compact('question'), 200);
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function updateQuestion(Request $request, $id)
    {
        $question_type = $request->question_type;
        $question = Question::find($id);
        $question->question = $request->question;
        $question->optA = $request->optA;
        $question->optB = $request->optB;
        $question->optC = $request->optC;
        $question->optD = $request->optD;
        $question->answer = $request->answer;
        $question->question_type = $request->question_type;
        $question->point = $request->point;
        $question->save();

        return response()->json(compact('question'), 200);
    }
    /**
     * Show the specified resource.
     * @return Response
     */
    private function compileQuiz(Request $request)
    {

        $compilation = new QuizCompilation();
        $compilation->school_id = $this->getSchool()->id;
        $compilation->instructions = $request->instructions;
        $compilation->question_type = $request->question_type;
        $compilation->duration = $request->duration;
        $compilation->point = $request->point;
        $compilation->status = $request->status;
        $compilation->exam_code = randomcode();
        $compilation->save();

        return $compilation;
    }
    /**
     * Show the specified resource.
     * @return Response
     */
    public function setQuiz(Request $request)
    {
        $compilation = $this->compileQuiz($request);

        $question_ids = $request->question_ids;
        $quizzes = [];
        foreach ($question_ids as $interview_question_id) {
            $quiz = new Quiz();
            $quiz->school_id = $this->getSchool()->id;
            $quiz->interview_question_id = $interview_question_id;
            $quiz->interview_compilation_id = $compilation->id;

            $quiz->save();
            $quizzes[] = $quiz;
        }
        $compilation->quizzes = $quizzes;
        //$quiz_compilations = QuizCompilation::where('subject_teacher_id', $request->subject_teacher_id)->get();

        return response()->json(compact('compilation'), 200);
    }
    public function activateQuiz(Request $request, $id)
    {
        $compilation = QuizCompilation::find($id);

        // $compilation->instructions = $request->instructions;
        // $compilation->duration = $request->duration;
        // $compilation->point = $request->point;
        $compilation->status = $request->status;
        $compilation->save();

        return response()->json(compact('compilation'), 200);
    }
    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function updateQuiz(Request $request, $id)
    {
        $compilation = QuizCompilation::find($id);
        $compilation->quizzes()->delete();
        $compilation->instructions = $request->instructions;
        $compilation->duration = $request->duration;
        $compilation->point = $request->point;
        $compilation->status = $request->status;
        $compilation->save();

        $question_ids = $request->question_ids;
        $quizzes = [];
        foreach ($question_ids as $question_id) {
            $quiz = new Quiz();
            $quiz->school_id = $this->getSchool()->id;
            $quiz->subject_teacher_id = $request->subject_teacher_id;
            $quiz->interview_question_id = $question_id;
            $quiz->interview_compilation_id = $compilation->id;

            $quiz->save();
            $quizzes[] = $quiz;
        }
        $compilation = $compilation->with(['quizzes', 'quizAttempts.quizAnswers.question', 'quizAttempts.quizAnswers.theoryQuestion', 'quizAttempts.user'])->find($compilation->id);
        return response()->json(compact('compilation'), 200);
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function deleteQuiz(Request $request, $id)
    {
        $compilation = QuizCompilation::find($id);
        $compilation->quizAnswers()->delete();
        $compilation->quizAttempts()->delete();
        $compilation->quizzes()->delete();
        $compilation->delete();

        return response()->json(['message' => 'success'], 200);
    }
}

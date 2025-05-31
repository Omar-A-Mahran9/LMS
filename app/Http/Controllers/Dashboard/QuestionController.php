<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreQuestionRequest;
use App\Http\Requests\Dashboard\StoreQuizRequest;
use App\Http\Requests\Dashboard\UpdateQuestionRequest;
use App\Http\Requests\Dashboard\UpdateQuizRequest;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
   public function index(Request $request)
    {
        $this->authorize('view_quizzes');
        // Total quizzes count
        $count_quizzes = QuizQuestion::count();

        // Example static value (customize as needed)
        $total_attempts = 1000;

        // Get all published courses and sections
        $courses = Course::where('is_active', 1)->get();
        $sections = CourseSection::get();
        $quizzes=Quiz::get();
         if ($request->ajax()) {
            // Return JSON data (use getModelData helper if you have it set up)
            return response()->json(getModelData(
                model: new QuizQuestion(),
                relations: [
                        'quiz' => ['id', 'title_en', 'title_ar'],
                        'answers' => ['id', 'quiz_question_id', 'answer_ar', 'answer_en', 'is_correct']
                    ]
            ));
        } else {
            // Return Blade view with variables
            return view('dashboard.questions.index', compact(
                'count_quizzes',
                'total_attempts',
                'courses',
                'sections',
                'quizzes',
            ));
        }
    }






 public function store(StoreQuestionRequest $request)
{
    $this->authorize('create_quizzes');

    // Create the question
    $question = QuizQuestion::create([
        'quiz_id'     => $request->quiz_id,
        'question_ar' => $request->question_ar,
        'question_en' => $request->question_en,
        'type'        => $request->type,
        'points'      => $request->points ?? 1,
    ]);

    // Depending on question type, handle answers
    if ($request->type === 'multiple_choice') {
        // Save multiple choice answers
        foreach ($request->answers as $answerData) {
            $question->answers()->create([
                'answer_ar' => $answerData['text_ar'],
                'answer_en' => $answerData['text_en'],
                'is_correct' => isset($answerData['is_correct']) && $answerData['is_correct'] ? true : false,
            ]);
        }
    } elseif ($request->type === 'true_false') {
        // For true/false, create two answers, mark one correct
        $correctValue = $request->correct_tf === 'true';

        $question->answers()->createMany([
            [
                'answer_ar' => 'صحيح',
                'answer_en' => 'True',
                'is_correct' => $correctValue,
            ],
            [
                'answer_ar' => 'خطأ',
                'answer_en' => 'False',
                'is_correct' => !$correctValue,
            ],
        ]);
    } elseif ($request->type === 'short_answer') {

        $question->expected_answer = $request->short_answer;
        $question->save();
    }
    // return redirect()->route('dashboard.quizzes.index');

}


public function update(UpdateQuestionRequest $request, QuizQuestion $question)
{
    $this->authorize('update_quizzes');

    // Update base fields
    $question->update([
        'quiz_id'     => $request->quiz_id,
        'question_ar' => $request->question_ar,
        'question_en' => $request->question_en,
        'type'        => $request->type,
        'points'      => $request->points ?? 1,
    ]);

    // Delete old answers
    $question->answers()->delete();

    // Handle each type
    if ($request->type === 'multiple_choice') {
        foreach ($request->answers as $answerData) {
            $question->answers()->create([
                'answer_ar'  => $answerData['text_ar'],
                'answer_en'  => $answerData['text_en'],
                'is_correct' => isset($answerData['is_correct']) && $answerData['is_correct'],
            ]);
        }
    } elseif ($request->type === 'true_false') {
        $correct = $request->correct_tf === 'true';

        $question->answers()->createMany([
            ['answer_ar' => 'صحيح', 'answer_en' => 'True',  'is_correct' => $correct],
            ['answer_ar' => 'خطأ',  'answer_en' => 'False', 'is_correct' => !$correct],
        ]);
    } elseif ($request->type === 'short_answer') {
        $question->expected_answer = $request->short_answer;
        $question->save(); // update expected_answer only
    }


}


    public function destroy(QuizQuestion $question)
    {
        $this->authorize('delete_quizzes');

        $question->delete();

        return response()->json(['message' => __('question deleted successfully.')]);
    }

}

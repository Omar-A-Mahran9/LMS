<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreQuestionRequest;
use App\Http\Requests\Dashboard\StoreQuizRequest;
use App\Http\Requests\Dashboard\UpdateQuizRequest;

use App\Models\Quiz;
use App\Models\QuizQuestion;

class QuestionController extends Controller
{
   public function index(Quiz $quiz)
   {
        $this->authorize('view_quizzes');

        return view('dashboard.questions.index', [
            'quiz' => $quiz,
            'questions' => $quiz->questions // assumes relation exists
        ]);
   }



     public function show(Quiz $quiz)
   {
        $this->authorize('view_quizzes');

        return view('dashboard.questions.index', [
            'quiz' => $quiz,
            'questions' => $quiz->questions // assumes relation exists
        ]);
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
}


    public function update(UpdateQuizRequest $request, Quiz $quiz)
    {
        $this->authorize('update_quizzes');

        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        $quiz->update($validated);

        return response()->json(['message' => __('Quiz updated successfully.')]);
    }

    public function destroy(Quiz $quiz)
    {
        $this->authorize('delete_quizzes');

        $quiz->delete();

        return response()->json(['message' => __('Quiz deleted successfully.')]);
    }

}

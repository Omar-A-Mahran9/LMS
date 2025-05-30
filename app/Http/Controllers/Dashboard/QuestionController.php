<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreQuestionRequest;
use App\Http\Requests\Dashboard\StoreQuizRequest;
use App\Http\Requests\Dashboard\UpdateQuizRequest;

use App\Models\Quiz;

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

        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        Quiz::create($validated);

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

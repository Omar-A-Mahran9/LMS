<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreQuizRequest;
use App\Http\Requests\Dashboard\UpdateQuizRequest;
use App\Models\Admin;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index(Request $request)
    {

        $this->authorize('view_quizzes');
        // Total quizzes count
        $count_quizzes = Quiz::count();

        // Example static value (customize as needed)
        $total_attempts = 1000;

        // Get all published courses and sections
        $courses = Course::where('is_active', 1)->get();
        $sections = CourseSection::get();

        if ($request->ajax()) {
            // Return JSON data (use getModelData helper if you have it set up)
            return response()->json(getModelData(
                model: new Quiz(),
                relations: ['course' => ['id', 'title_en', 'title_ar'], 'section' => ['id', 'title_en', 'title_ar']]
            ));
        } else {
            // Return Blade view with variables
            return view('dashboard.quizzes.index', compact(
                'count_quizzes',
                'total_attempts',
                'courses',
                'sections'
            ));
        }
    }




    public function store(StoreQuizRequest $request)
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

<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreQuizRequest;
use App\Http\Requests\Dashboard\UpdateQuizRequest;
use App\Models\HomeWork;
use App\Models\Quiz;
use Illuminate\Http\Request;

class HomeworkByClassController extends Controller
{
public function index(Request $request, $classId)
    {
             $this->authorize('view_homework');
            if ($request->ajax()) {
                // Return JSON data (use getModelData helper if you have it set up)
                return response()->json(getModelData(
                    model: new HomeWork(),
                     andsFilters: [['class_id', '=', $classId]],
                    relations: ['course' => ['id', 'title_en', 'title_ar'], 'class' => ['id', 'title_en', 'title_ar']]
                ));
            } else {
                // Return Blade view with variables
                return view('dashboard.homework.index');
            }
        }

public function store(StoreQuizRequest $request, $classId)
{
    $this->authorize('create_homework');

    // Check if a quiz already exists for this class
    $exists = HomeWork::where('class_id', $classId)->exists();

    if ($exists) {
        return $this->failure(__('A homework already exists for this class.'));
    }

    $validated = $request->validated();
    $validated['class_id'] = $classId;
    $validated['is_active'] = $request->boolean('is_active');

    HomeWork::create($validated);

    return response()->json(['message' => __('HomeWork created successfully.')]);
}


public function update(UpdateQuizRequest $request, $classId, Quiz $quiz)
{
    $this->authorize('update_quizzes');

    $validated = $request->validated();
    $validated['is_active'] = $request->boolean('is_active');

    $quiz->update($validated);

    return response()->json(['message' => __('Quiz updated successfully.')]);
}

public function destroy($classId, Quiz $quiz)
{
    $this->authorize('delete_quizzes');

    $quiz->delete();

    return response()->json(['message' => __('Quiz deleted successfully.')]);
}


}

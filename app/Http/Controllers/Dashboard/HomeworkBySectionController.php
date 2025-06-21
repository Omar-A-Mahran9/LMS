<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreQuizRequest;
use App\Http\Requests\Dashboard\UpdateQuizRequest;
use App\Models\HomeWork;
use App\Models\Quiz;
use Illuminate\Http\Request;

class HomeworkBySectionController extends Controller
{
public function index(Request $request, $sectionId)
    {
             $this->authorize('view_homework');
            if ($request->ajax()) {
                // Return JSON data (use getModelData helper if you have it set up)
                return response()->json(getModelData(
                    model: new HomeWork(),
                     andsFilters: [['section_id', '=', $sectionId]],
                    relations: ['course' => ['id', 'title_en', 'title_ar'], 'section' => ['id', 'title_en', 'title_ar']]
                ));
            } else {
                // Return Blade view with variables
                return view('dashboard.homework.index');
            }
        }

public function store(StoreQuizRequest $request, $sectionId)
{
    $this->authorize('create_homework');

    // Check if a quiz already exists for this class
    $exists = HomeWork::where('section_id', $sectionId)->exists();

    if ($exists) {
        return $this->failure(__('A homework already exists for this class.'));
    }

    $validated = $request->validated();
    $validated['section_id'] = $sectionId;
    $validated['is_active'] = $request->boolean('is_active');

    HomeWork::create($validated);

    return response()->json(['message' => __('HomeWork created successfully.')]);
}


public function update(UpdateQuizRequest $request, $sectionId, Quiz $quiz)
{
    $this->authorize('update_quizzes');

    $validated = $request->validated();
    $validated['is_active'] = $request->boolean('is_active');

    $quiz->update($validated);

    return response()->json(['message' => __('Quiz updated successfully.')]);
}

public function destroy($sectionId, $homeworkId)
{
    $homework = HomeWork::find($homeworkId);
     if (!$homework) {
        return response()->json(['message' => __('Homework not found.')], 404);
    }

    $this->authorize('delete_homework');

    $homework->delete();

    return response()->json(['message' => __('Homework deleted successfully.')]);
}



}

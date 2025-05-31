<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreHomeWorkRequest;
use App\Http\Requests\Dashboard\UpdateHomeWorkRequest;
 use App\Models\Course;
use App\Models\CourseSection;
use App\Models\HomeWork;
 use Illuminate\Http\Request;

class HomeWorkController extends Controller
{
    public function index(Request $request)
    {

        $this->authorize('view_homework');
        // Total homeworkzes count
        $count_homework = HomeWork::count();

        // Example static value (customize as needed)
        $total_attempts = 1000;

        // Get all published courses and sections
        $courses = Course::where('is_active', 1)->get();
        $sections = CourseSection::get();

        if ($request->ajax()) {
            // Return JSON data (use getModelData helper if you have it set up)
            return response()->json(getModelData(
                model: new HomeWork(),
                relations: ['course' => ['id', 'title_en', 'title_ar'], 'section' => ['id', 'title_en', 'title_ar']]
            ));
        } else {
            // Return Blade view with variables
            return view('dashboard.homeworks.index', compact(
                'count_homework',
                'total_attempts',
                'courses',
                'sections'
            ));
        }
    }




    public function store(StoreHomeWorkRequest $request)
    {
        $this->authorize('create_homework');

        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        HomeWork::create($validated);

    }

    public function update(UpdateHomeWorkRequest $request, HomeWork $homework)
    {
        $this->authorize('update_homework');

        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        $homework->update($validated);

        return response()->json(['message' => __('homework updated successfully.')]);
    }

    public function destroy(HomeWork $homework)
    {
        $this->authorize('delete_homework');

        $homework->delete();

        return response()->json(['message' => __('homework deleted successfully.')]);
    }

}

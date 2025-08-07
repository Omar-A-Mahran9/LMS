<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreLiveRequest;
use App\Http\Requests\Dashboard\UpdateLiveRequest;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Live;
use Illuminate\Http\Request;

class LiveController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_lives');

        $courseId = $request->input('course_id');
        $courses = Course::select('id', 'title_en', 'title_ar')->get();
        $classes=CourseClass::select('id', 'title_en', 'title_ar')->get();

        if ($request->ajax()) {
        return  getModelData(model:new Live());

        }

        return view('dashboard.lives.index', compact('courses','classes'));
    }

    public function store(StoreLiveRequest $request)
    {
        $this->authorize('create_lives');

        $validated = $request->validated();

        $validated['chat_enabled'] = $request->boolean('chat_enabled');
        $validated['is_recorded'] = $request->boolean('is_recorded');
        $validated['is_active'] = $request->boolean('is_active');

        $live = Live::create($validated);

     }

    public function update(UpdateLiveRequest $request, $id)
    {
        $this->authorize('update_lives');
        $live = Live::findOrFail($id);
        $validated = $request->validated();

        $validated['chat_enabled'] = $request->boolean('chat_enabled');
        $validated['is_recorded'] = $request->boolean('is_recorded');
        $validated['is_active'] = $request->boolean('is_active');

        $live->update($validated);

        return response()->json(['status' => true, 'message' => __('Live session updated successfully.')]);
    }

    public function show($id)
    {
        $this->authorize('view_lives');

        $live = Live::with(['course:id,title_en,title_ar', 'class:id,title_en,title_ar'])->findOrFail($id);
        $courses = Course::select('id', 'title_en', 'title_ar')->get();
        $classes = CourseClass::select('id', 'title_en', 'title_ar')->get();

        return view('dashboard.lives.show', compact('live', 'courses', 'classes'));
    }

    public function destroy($id)
    {
        $this->authorize('delete_lives');

        $live = Live::findOrFail($id);
        $live->delete();

        return response()->json([
            'status' => true,
            'message' => __('Live session deleted successfully.'),
        ]);
    }
}

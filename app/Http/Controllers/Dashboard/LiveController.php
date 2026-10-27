<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreLiveRequest;
use App\Http\Requests\Dashboard\UpdateLiveRequest;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Live;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LiveController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_lives');

        $courses = Course::with('category')->select('id', 'title_en', 'title_ar', 'category_id')->get();
        $classes=CourseClass::with('course')->select('id', 'title_en', 'title_ar', 'course_id')->get();

        if ($request->ajax()) {
        return  getModelData(model:new Live());

        }

        return view('dashboard.lives.index', compact('courses','classes'));
    }

    public function store(StoreLiveRequest $request)
    {
        $this->authorize('create_lives');

        $validated = $this->prepareData($request, $request->validated());

        if ($request->hasFile('image')) {
            $validated['image'] = uploadImageToDirectory($request->file('image'), 'Lives');
        }
        $live = Live::create($validated);

        return response()->json([
            'message' => __('Live created successfully'),
            'data' => $live,
        ]);
    }

  public function update(UpdateLiveRequest $request,  $id)
{
    $live=Live::findOrFail($id);
    $this->authorize('update_lives');

    $validated = $this->prepareData($request, $request->validated());

    if ($request->hasFile('image')) {
        deleteImageFromDirectory($live->image,'Lives');
        $validated['image'] = uploadImageToDirectory($request->file('image'), 'Lives');
    }
     $live->update($validated);

    return response()->json([
        'message' => __('Live updated successfully'),
        'data' => $live,
    ]);
}


    public function show($id)
    {
        $this->authorize('view_lives');

        $live = Live::findOrFail($id);

        // The live's content (quizzes, homework, videos) is managed from its class page
        if ($live->class_id) {
            return redirect()->route('dashboard.classes.show', $live->class_id);
        }

        return redirect()->route('dashboard.lives.index');
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

    /**
     * Flags from the switches, and the end time / duration filled from each other
     * (60 minutes when both are empty). Times are kept as entered (Egypt time).
     */
    private function prepareData(Request $request, array $validated): array
    {
        $validated['chat_enabled'] = $request->boolean('chat_enabled');
        $validated['is_recorded'] = $request->boolean('is_recorded');
        $validated['is_active'] = $request->boolean('is_active');

        $start = Carbon::parse($validated['start_time']);
        $validated['start_time'] = $start->format('Y-m-d H:i:s');

        if (!empty($validated['end_time'])) {
            $end = Carbon::parse($validated['end_time']);
            $validated['duration_minutes'] = (int) $start->diffInMinutes($end);
        } else {
            $validated['duration_minutes'] = $validated['duration_minutes'] ?? 60;
            $end = $start->copy()->addMinutes($validated['duration_minutes']);
        }
        $validated['end_time'] = $end->format('Y-m-d H:i:s');

        return $validated;
    }
}

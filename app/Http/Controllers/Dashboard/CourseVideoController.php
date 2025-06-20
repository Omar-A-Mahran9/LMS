<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreVideoRequest;
use App\Http\Requests\Dashboard\UpdateVideoRequest;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\CourseVideo;
use App\Models\Quiz;
use Illuminate\Http\Request;

class CourseVideoController extends Controller
{
    public function index(Request $request)
    {
         $this->authorize('view_videos');

        // Count total courses
        $count_addon = CourseVideo::count();
        $courses = Course::select('id', 'title_en', 'title_ar')->get();

        $quizzes = Quiz::select('id', 'title_en', 'title_ar')->get();

        // Example static visited site count (you may want to make this dynamic)
        $visited_site = 10000;

        if ($request->ajax()) {
            // Return JSON data for AJAX requests
            return response()->json(getModelData(model: new CourseVideo(),relations: ['course' => ['id', 'title_ar','title_en' ],'class' => ['id', 'title_ar','title_en' ]]));
        } else {
            // Return the main view with data
            return view('dashboard.videos.index', compact( 'visited_site','courses','quizzes'));
        }
    }
    public function getvideosbyclasses(Request $request,$classId)
    {
          $this->authorize('view_videos');

        // Count total courses
         $courses = Course::select('id', 'title_en', 'title_ar')->get();

        $quizzes = Quiz::select('id', 'title_en', 'title_ar')->get();

        if ($request->ajax()) {
            // Return JSON data for AJAX requests
            return response()->json(getModelData(model: new CourseVideo(), andsFilters: [['class_id', '=', $classId]],relations: ['course' => ['id', 'title_ar','title_en' ],'class' => ['id', 'title_ar','title_en' ]]));
        } else {
            // Return the main view with data
            return view('dashboard.videos.index', compact('courses','quizzes'));
        }
    }


  public function store(StoreVideoRequest $request)
    {
        $this->authorize('create_videos');

        $validated = $request->validated();
    // Handle image uploads
        if ($request->hasFile('image')) {
            $validated['image'] = uploadImageToDirectory($request->file('image'), 'courses_videos');
        }

        // Auto fetch YouTube duration if duration is not provided
        if (empty($validated['duration_seconds'])) {
            $validated['duration_seconds'] = 0;
        }

        // Set default values for checkboxes
        $validated['is_preview'] = $request->boolean('is_preview');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['quiz_required'] = $request->boolean('quiz_required');

        $video = CourseVideo::create($validated);

    }


public function update(UpdateVideoRequest $request, $id)
{
    $this->authorize('update_videos');
$courseVideo=CourseVideo::find($id);
    $validated = $request->validated();

    // Handle image update
    if ($request->hasFile('image')) {
        // Optionally: delete the old image if needed
        // deleteFile($courseVideo->image);

        $validated['image'] = uploadImageToDirectory($request->file('image'), 'courses_videos');
    }

    // Auto-fetch YouTube duration if not provided
    if (empty($validated['duration_seconds']) && !empty($validated['video_url'])) {
        $validated['duration_seconds'] = 0;
    }

    // Set boolean flags
    $validated['is_preview'] = $request->boolean('is_preview');
    $validated['is_active'] = $request->boolean('is_active');
    $validated['quiz_required'] = $request->boolean('quiz_required');

    $courseVideo->update($validated);


}

public function show($id)
{
    $this->authorize('view_videos'); // Optional: permission check

    $video = CourseVideo::findOrFail($id);

    return view('dashboard.videos.show', compact('video'));
}

public function destroy( $id)
{
    $this->authorize('delete_videos');
    $courseVideo=CourseVideo::find($id);
    // Optionally delete the associated image file
    if ($courseVideo->image) {
        deleteImageFromDirectory($courseVideo->image, 'courses_videos'); // This should be your helper function to delete a file
    }

    $courseVideo->delete();

    return response()->json([
        'status' => true,
        'message' => __('Course video deleted successfully.'),
    ]);
}

}

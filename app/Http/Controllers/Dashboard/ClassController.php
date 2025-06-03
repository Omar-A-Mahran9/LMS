<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreClassRequest;
use App\Http\Requests\Dashboard\StoreVideoRequest;
use App\Http\Requests\Dashboard\UpdateClassRequest;
use App\Http\Requests\Dashboard\UpdateVideoRequest;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\CourseSection;
use App\Models\CourseVideo;
use App\Models\Quiz;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index(Request $request)
    {
         $this->authorize('view_classes');

        // Count total courses
        $count_addon = CourseVideo::count();
        $courses = Course::select('id', 'title_en', 'title_ar')->get();
        // Example static visited site count (you may want to make this dynamic)
        $visited_site = 10000;

        if ($request->ajax()) {
            // Return JSON data for AJAX requests
            return response()->json(getModelData(model: new CourseClass(),relations: ['course' => ['id', 'title_ar','title_en' ]]));
        } else {
            // Return the main view with data
            return view('dashboard.classes.index', compact( 'visited_site','courses'));
        }
    }



  public function store(StoreClassRequest $request)
{
    $this->authorize('create_classes');

     $validated = $request->validated();
   // Handle image uploads
    if ($request->hasFile('image')) {
        $validated['image'] = uploadImageToDirectory($request->file('image'), 'courses_classes');
    }
    if ($request->hasFile('attachment')) {
    $validated['attachment'] = uploadAttachmentToDirectory($request->file('attachment'), 'courses_classes');
    }
    // Set default values for checkboxes
    $validated['is_preview'] = $request->boolean('is_preview');
    $validated['is_active'] = $request->boolean('is_active');
    $validated['quiz_required'] = $request->boolean('quiz_required');

    $CourseClass = CourseClass::create($validated);

}


public function update(UpdateClassRequest $request, $id)
{
    $this->authorize('update_classes');
    $courseClass=CourseClass::find($id);
    $validated = $request->validated();

    // Handle image update
    if ($request->hasFile('image')) {
        if ($courseClass->image) {
            deleteImageFromDirectory($courseClass->image, 'courses_classes');
        }

        $validated['image'] = uploadImageToDirectory($request->file('image'), 'courses_classes');
    }

    // Handle attachment update
    if ($request->hasFile('attachment')) {
        if ($courseClass->attachment) {
            deleteAttachmentFromDirectory($courseClass->attachment, 'courses_classes');
        }

        $validated['attachment'] = uploadAttachmentToDirectory($request->file('attachment'), 'courses_classes');
    }


    // Set boolean flags
    $validated['is_preview'] = $request->boolean('is_preview');
    $validated['is_active'] = $request->boolean('is_active');
    $validated['quiz_required'] = $request->boolean('quiz_required');

    $courseClass->update($validated);
}

public function show($id)
{
    $class = CourseClass::findOrFail($id); // Prefer findOrFail for proper error handling
    $this->authorize('view_classes');

    $class->load(['course']); // Preload course relation

    return view('dashboard.classes.show', compact('class'));
}

public function destroy( $id)
{
    $this->authorize('delete_classes');
    $courseVideo=CourseClass::find($id);
    // Optionally delete the associated image file
    if ($courseVideo->image) {
        deleteImageFromDirectory($courseVideo->image, 'courses_classes'); // This should be your helper function to delete a file
    }

    $courseVideo->delete();

    return response()->json([
        'status' => true,
        'message' => __('Course class deleted successfully.'),
    ]);
}

}

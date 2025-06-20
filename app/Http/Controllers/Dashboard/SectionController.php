<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreClassRequest;
use App\Http\Requests\Dashboard\StoreSectionRequest;
use App\Http\Requests\Dashboard\UpdateClassRequest;
 use App\Models\Course;
use App\Models\CourseClass;
 use App\Models\CourseVideo;
use App\Models\Quiz;
use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index(Request $request)
    {
          $this->authorize('view_sections');


        $courses = Course::select('id', 'title_en', 'title_ar')->get();
        // Example static visited site count (you may want to make this dynamic)
        $visited_site = 10000;

        if ($request->ajax()) {
            // Return JSON data for AJAX requests
            return response()->json(getModelData(model: new Section(),relations: ['course' => ['id', 'title_ar','title_en' ]]));
        } else {

            // Return the main view with data
            return view('dashboard.sections.index', compact( 'courses'));
        }
    }



  public function store(StoreSectionRequest $request)
    {
        $this->authorize('create_sections');

        $validated = $request->validated();
    // Handle image uploads
        if ($request->hasFile('image')) {
            $validated['image'] = uploadImageToDirectory($request->file('image'), 'sections');
        }
        if ($request->hasFile('attachment')) {
        $validated['attachment'] = uploadAttachmentToDirectory($request->file('attachment'), 'sections');
        }
        // Set default values for checkboxes
        $validated['is_preview'] = $request->boolean('is_preview');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['quiz_required'] = $request->boolean('quiz_required');

        $CourseClass = Section::create($validated);

    }


    public function update(UpdateClassRequest $request, Section $section)
    {
        $this->authorize('update_sections');
         $validated = $request->validated();

        // Handle image update
        if ($request->hasFile('image')) {
            if ($section->image) {
                deleteImageFromDirectory($section->image, 'sections');
            }

            $validated['image'] = uploadImageToDirectory($request->file('image'), 'sections');
        }

        // Handle attachment update
        if ($request->hasFile('attachment')) {
            if ($section->attachment) {
                deleteAttachmentFromDirectory($section->attachment, 'sections');
            }

            $validated['attachment'] = uploadAttachmentToDirectory($request->file('attachment'), 'sections');
        }


        // Set boolean flags
        $validated['is_preview'] = $request->boolean('is_preview');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['quiz_required'] = $request->boolean('quiz_required');

        $section->update($validated);
    }

public function show($id)
{
    $section = Section::with('course')->findOrFail($id); // Eager load course
    $this->authorize('view_sections');
        $courses = Course::select('id', 'title_en', 'title_ar')->get();

        $quizzes = Quiz::select('id', 'title_en', 'title_ar')->get();

    $quizExists = $section->quizzes()->exists(); // Assumes you have quizzes() relationship in CourseClass model
    $homeworskExists = $section->homeworks()->exists(); // Assumes you have quizzes() relationship in CourseClass model

    return view('dashboard.sections.show', compact('section', 'quizExists','courses','quizzes','homeworskExists'));
}


public function destroy(Section $section)
{
    $this->authorize('delete_sections');
     // Optionally delete the associated image file
    if ($section->image) {
        deleteImageFromDirectory($section->image, 'sections'); // This should be your helper function to delete a file
    }

    $section->delete();

    return response()->json([
        'status' => true,
        'message' => __('Course class deleted successfully.'),
    ]);
}

}

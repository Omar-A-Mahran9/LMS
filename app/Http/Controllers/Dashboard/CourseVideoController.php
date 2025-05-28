<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreVideoRequest;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\CourseVideo;
use Illuminate\Http\Request;

class CourseVideoController extends Controller
{
    public function index(Request $request)
    {
         $this->authorize('view_videos');

        // Count total courses
        $count_addon = CourseVideo::count();
        $courses = Course::select('id', 'title_en')->get();
          $sections = CourseSection::select('id', 'title_en')->get();

        // Example static visited site count (you may want to make this dynamic)
        $visited_site = 10000;

        if ($request->ajax()) {
            // Return JSON data for AJAX requests
            return response()->json(getModelData(model: new CourseVideo()));
        } else {
            // Return the main view with data
            return view('dashboard.videos.index', compact( 'visited_site','courses','sections'));
        }
    }



  public function store(StoreVideoRequest $request)
{
    $this->authorize('store_videos');

     $validated = $request->validated();
   // Handle image uploads
    if ($request->hasFile('image')) {
        $data['image'] = uploadImageToDirectory($request->file('image'), 'courses_videos');
    }

    // Auto fetch YouTube duration if duration is not provided
    if (empty($validated['duration_seconds'])) {
        $validated['duration_seconds'] = 0;
    }

    // Set default values for checkboxes
    $validated['is_preview'] = $request->boolean('is_preview');
    $validated['is_active'] = $request->boolean('is_active');

    $video = CourseVideo::create($validated);
 
}

    /**
     * Display the specified resource.
     */
    public function show(CourseVideo $courseVideo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CourseVideo $courseVideo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CourseVideo $courseVideo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CourseVideo $courseVideo)
    {
        //
    }
}

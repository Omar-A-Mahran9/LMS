<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreCourseRequest;
use App\Http\Requests\Dashboard\UpdateAddonRequest;
use App\Http\Requests\Dashboard\UpdateCourseRequest;
use App\Models\Admin;
use App\Models\Category;
use App\Models\CategorySubCategory;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseQuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
            $this->authorize('view_courses');

    // Count total courses
    $count_addon = Course::count();

    // Example static visited site count (you may want to make this dynamic)
    $visited_site = 10000;

    // If instructors are needed on the page, fetch get
    $instructors = Admin::where('type','instructor')->get(); // or whatever your instructor model is
    // Get only published categories that are parent-level or subcategories if needed
    $categories = Category::where('is_publish', 1)
        ->get();
    $subcategories = CategorySubCategory::where('is_publish', 1)
        ->get();

    if ($request->ajax()) {
        // Return JSON data for AJAX requests
        return response()->json(getModelData(model: new Course(),relations: ['instructor' => ['id', 'name' ]]));
    } else {
        // Return the main view with data
        return view('dashboard.courses.index', compact('categories', 'visited_site', 'instructors','subcategories'));
    }
}



public function store(StoreCourseRequest $request)
{
    $this->authorize('create_courses');

     $data = $request->validated();

    // Handle image uploads
    if ($request->hasFile('image')) {
        $data['image'] = uploadImageToDirectory($request->file('image'), 'courses');
    }

    if ($request->hasFile('slide_image')) {
        $data['slide_image'] = uploadImageToDirectory($request->file('slide_image'), 'courses/slides');
    }

    // If course is free, set price to 0
    if ($request->filled('is_free') && $request->boolean('is_free')) {
        $data['price'] = 0;
    }

    // If course doesn't have a discount, remove discount field
    if (!$request->boolean('have_discount')) {
        $data['discount_percentage'] = null;
    }

    // Handle boolean flags
    $data['is_free'] = $request->boolean('is_free');
    $data['have_discount'] = $request->boolean('have_discount');
    $data['is_enrollment_open'] = $request->boolean('is_enrollment_open');
    $data['show_in_home'] = $request->boolean('show_in_home');
    $data['featured'] = $request->boolean('featured');
    $data['certificate_available'] = $request->boolean('certificate_available');

    // Create course
    $course =Course::create($data);

    // Attach subcategories if any
    if ($request->filled('subcategory_ids')) {
        $course->subCategories()->sync($request->input('subcategory_ids'));
    }


}




public function update(UpdateCourseRequest $request, Course $course)
{
            $this->authorize('update_courses');

        $data = $request->validated();
    unset($data['subcategory_ids']);
    if ($request->hasFile('image')) {
        $data['image'] = uploadImageToDirectory($request->file('image'), 'courses');
    }

    if ($request->hasFile('slide_image')) {
        $data['slide_image'] = uploadImageToDirectory($request->file('slide_image'), 'courses/slides');
    }

    // If course is free, set price to 0
    if ($request->filled('is_free') && $request->boolean('is_free')) {
        $data['price'] = 0;
    }

    // If course doesn't have a discount, remove discount field
    if (!$request->boolean('have_discount')) {
        $data['discount_percentage'] = null;
    }

    // Handle boolean flags
    $data['is_free'] = $request->boolean('is_free');
    $data['have_discount'] = $request->boolean('have_discount');
    $data['is_enrollment_open'] = $request->boolean('is_enrollment_open');
    $data['show_in_home'] = $request->boolean('show_in_home');
    $data['featured'] = $request->boolean('featured');
    $data['certificate_available'] = $request->boolean('certificate_available');

    // Update course data
    $course->update($data);
    $course->subCategories()->sync([2, 3, 4]);

     // Sync subcategories if any
    if ($request->filled('subcategory_ids')) {
        $course->subCategories()->sync($request->input('subcategory_ids', []));
    } else {
        // If none sent, detach all
        $course->subCategories()->detach();
    }

    return response()->json([
        'status' => true,
        'message' => __('Course updated successfully.'),
        'data' => $course,
    ]);
}

public function show(Course $course)
{
    // Authorize if needed
    $this->authorize('view_courses');

    // Load related data: category, subCategories, instructor
    $course->load(['category', 'subCategories', 'instructor']);

    return view('dashboard.courses.show', compact('course'));
}


    public function destroy($id)
    {
        $course=Course::find($id);
        $this->authorize('delete_courses');
        $course->delete();
        return response(["Course deleted successfully"]);

    }


    public function deleteSelected(Request $request)
    {
        $this->authorize('delete_courses');

        Course::whereIn('id', $request->selected_items_ids)->delete();

        return response(["selected services deleted successfully"]);
    }

    public function restoreSelected(Request $request)
    {
        $this->authorize('delete_courses');
        Course::withTrashed()->whereIn('id', $request->selected_items_ids)->restore();

        return response(["selected services restored successfully"]);
    }
}

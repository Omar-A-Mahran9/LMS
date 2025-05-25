<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreCourseRequest;
use App\Http\Requests\Dashboard\UpdateAddonRequest;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    // Count total courses
    $count_addon = Course::count();

    // Example static visited site count (you may want to make this dynamic)
    $visited_site = 10000;

    // If instructors are needed on the page, fetch get
    $instructors = Admin::where('type','instructor')->get(); // or whatever your instructor model is
    // Get only published categories that are parent-level or subcategories if needed
    $categories = Category::where('is_publish', 1)
        ->get();

    if ($request->ajax()) {
        // Return JSON data for AJAX requests
        return response()->json(getModelData(model: new Course()));
    } else {
        // Return the main view with data
        return view('dashboard.courses.index', compact('categories', 'visited_site', 'instructors'));
    }
}



    public function store(StoreCourseRequest $request)
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = uploadImageToDirectory($request->file('image'), "Courses");
        }

        // Handle icon upload
        if ($request->hasFile('icon')) {
            $data['icon'] = uploadImageToDirectory($request->file('icon'), "Courses");
        }

        // is_publish toggle
        $data['is_publish'] = $request->has('is_publish') ? 1 : 0;

        // have_price_after_visiting toggle
        $data['have_price_after_visiting'] = $request->has('have_price_after_visiting') ? 1 : 0;

        // If have_price_after_visiting is true, nullify price
        if ($data['have_price_after_visiting']) {
            $data['price'] = null;
        }

        // Create the AddonService
        $addon = Course::create($data);

    }




    public function update(UpdateAddonRequest $request, Course $course)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = uploadImageToDirectory($request->file('image'), "Courses");
        }

        if ($request->hasFile('icon')) {
            $data['icon'] = uploadImageToDirectory($request->file('icon'), "Courses");
        }

        $data['is_publish'] = $request->has('is_publish') ? 1 : 0;

        $addon->update($data);


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

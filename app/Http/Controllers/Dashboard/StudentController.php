<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreStudentRequest;
use App\Http\Requests\Dashboard\UpdateCustomerRequest;
use App\Http\Requests\Dashboard\UpdateStudentRequest;
use App\Models\Category;
 use App\Models\Government;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_students');

        if ($request->ajax())
        {
            $data = getModelData(model: new Student());
            return response()->json($data);
        }
        $governments = Government::get();
        $categories = Category::where('is_publish',1)->get();

        return view('dashboard.students.index', compact('governments','categories'));

    }

    public function store(StoreStudentRequest $request)
    {
        $data          = $request->validated();
        $data['image'] = uploadImageToDirectory($request->file('image'), "Students");
        $data['block_flag']= false;
        Student::create($data);

        return response(["Student created successfully"]);
    }

 public function update(UpdateStudentRequest $request, Student $student)
{
    // Validate the request data with your form request
    $data = $request->validated();

    // If there's a new image uploaded, handle the upload and set the image path
    if ($request->hasFile('image')) {
        $data['image'] = uploadImageToDirectory($request->file('image'), "Students");
    }

    // Update the student record with the validated data
    $student->update($data);

    // Return a JSON response for AJAX success handling
    return response()->json(["message" => __("Student updated successfully")]);
}


    public function show(Student $student)
    {
        // Authorize if needed
        $this->authorize('view_students');

        // Load any related data if needed, e.g. government, category
        $student->load(['government', 'category']);

        return view('dashboard.students.show', compact('student'));
    }


    public function destroy(Student $student)
    {
        $this->authorize('delete_students');

        $student->delete();

        return response(["Student deleted successfully"]);
    }


    public function blocked(Request $request, Student $student)
    {
        $this->authorize('delete_students');
        if ($student->block_flag === 0)
        {
            $student->update([
                'block_flag' => true
            ]);
            return response(["Student blocked successfully"]);
        }
        if ($student->block_flag === 1)
        {
            $student->update([
                'block_flag' => false
            ]);
            return response(["Student un blocked successfully"]);
        }
    }


}

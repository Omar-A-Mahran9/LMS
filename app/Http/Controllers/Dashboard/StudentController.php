<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreStudentRequest;
use Illuminate\Support\Str; // ✅ أضفه هنا
use App\Http\Requests\Dashboard\UpdateStudentRequest;
use App\Models\Category;
 use App\Models\Government;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_students');

        dd($request->filter_combined);
        if ($request->ajax())
        {
    $andsFilters = [];

    if ($request->filled('filter_combined')) {
        $value = $request->filter_combined;

        if (Str::startsWith($value, 'category_')) {
            $categoryId = Str::after($value, 'category_');
            $andsFilters[] = ['category_id', '=', $categoryId];
        } elseif ($value === 'courses_only') {
            $andsFilters[] = ['category_id', '=', null]; // only courses (no category)
        } elseif ($value === 'classes_only') {
            $andsFilters[] = ['category_id', '!=', null]; // only classes (have category)
        }
        // 'all' = no filter applied
    }

            $data = getModelData(model: new Student(),
                    andsFilters: $andsFilters
);

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
    $this->authorize('view_students');

    $student->load([
        'government',
        'category',
        'courses' => ['sections', 'quizzes'], // لو كنت تحتاج مزيد من العلاقات
        'enrolledClasses.course',
        'quizAttempts.quiz',
        'homeworks.homework',
        'studentProgress.video'
    ]);

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
        // $this->authorize('delete_students');
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


    public function reportPdf(Student $student)
{
    $student->load([
        'government',
        'category',
        'courses',
        'quizAttempts.quiz',
        'homeworks.homework',
    ]);

    $quizStats = [
        'count' => $student->quizAttempts->count(),
        'average_score' => round($student->quizAttempts->avg('score'), 2),
    ];

    $homeworkStats = [
        'count' => $student->homeworks->count(),
        'submitted' => $student->homeworks->whereNotNull('submitted_at')->count(),
    ];

    $pdf = Pdf::loadView('dashboard.students.report_pdf', compact('student', 'quizStats', 'homeworkStats'))
        ->setPaper('a4', 'portrait');

    return $pdf->download("student_report_{$student->id}.pdf");
}


}

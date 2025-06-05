<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
       public function enroll_course(Request $request)
    {

        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $student = auth()->user(); // assuming the student is authenticated
        $course = Course::findOrFail($request->course_id);
        // Attach student to course if not already enrolled


        $exists_course = DB::table('course_student')
            ->where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->exists();


        if (!$exists_course) {

            $course->students()->attach($student->id);
        }

        return $this->success('',[
            'message' => 'Student enrolled successfully.',
        ]);
    }
    public function enroll_class(Request $request)
    {

        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $student = auth()->user(); // assuming the student is authenticated
       
        // Optional: attach to class if provided
        if ($request->filled('class_id')) {
            $class = CourseClass::findOrFail($request->class_id);

            $alreadyInClass = DB::table('class_student')
                ->where('class_id', $class->id)
                ->where('student_id', $student->id)
                ->exists();

            if (!$alreadyInClass) {
                $class->students()->attach($student->id);
            }

        }

        return $this->success('',[
            'message' => 'Student enrolled successfully.',
        ]);
    }
    public function enrollmentStatus($course_id)
    {
        $student = auth()->user();
        $course = Course::findOrFail($course_id);
        $enrolled = DB::table('course_student')
            ->where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->exists();
        return $this->success('',[
            'enrolled' => $enrolled,
        ]);
    }


}

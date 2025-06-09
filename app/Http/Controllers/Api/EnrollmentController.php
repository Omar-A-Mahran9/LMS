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
            'payment_type' => 'required|in:wallet_transfer,pay_in_center,contact_with_support',
        ]);

        $student = auth()->user();
        $course = Course::findOrFail($request->course_id);

        // Check if student already enrolled
        $enrollment = DB::table('course_student')
            ->where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->first();

        if ($enrollment) {
            return $this->success('Already enrolled.', [
                'is_enrolled' => true,
                'status' => $enrollment->status,
                'payment_type' => $enrollment->payment_type,
                'enrolled_at' => $enrollment->created_at,
            ]);
        }

        // Attach with pivot data
        $course->students()->attach($student->id, [
            'payment_type' => $request->payment_type,
            'status' => 'pending',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->success('Student enrolled successfully.', [
            'is_enrolled' => true,
            'status' => 'pending',
            'payment_type' => $request->payment_type,
            'enrolled_at' => now(),
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

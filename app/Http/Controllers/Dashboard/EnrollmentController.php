<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{

public function index(Request $request)
{
    $this->authorize('view_enrollments');

    if ($request->ajax()) {
        $search = $request->input('search.value'); // DataTables search input

        $query = Enrollment::query()
            ->leftJoin('students', 'course_student.student_id', '=', 'students.id')
            ->leftJoin('courses', 'course_student.course_id', '=', 'courses.id')
            ->select('course_student.*')
            ->with(['student:id,first_name,last_name,email,phone', 'course:id,title_ar,title_en']);

        // Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('students.first_name', 'LIKE', "%{$search}%")
                    ->orWhere('students.last_name', 'LIKE', "%{$search}%")
                    ->orWhere('students.email', 'LIKE', "%{$search}%")
                    ->orWhere('students.phone', 'LIKE', "%{$search}%")
                    ->orWhere('courses.title_ar', 'LIKE', "%{$search}%")
                    ->orWhere('courses.title_en', 'LIKE', "%{$search}%");
            });
        }

        return response()->json(
            getModelData(
                query: $query
            )
        );
    }

    $students = Student::get();
    $courses = Course::get();

    return view('dashboard.enrollments.index', compact('students', 'courses'));
}

public function getCoursesForStudent($studentId)
{
    // Find all courses that the student is NOT enrolled in
    $enrolledCourseIds = Enrollment::where('student_id', $studentId)->pluck('course_id')->toArray();

    // Fetch courses NOT in enrolled list and that are active (optional)
    $courses = Course::whereNotIn('id', $enrolledCourseIds)
                     ->where('is_active', 1)
                     ->get();

    return response()->json([
        'success' => true,
        'data' => $courses,
    ]);
}

public function changeStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:pending,approved,rejected'
    ]);

    $enrollment = Enrollment::findOrFail($id);
    $enrollment->status = $request->status;
    $enrollment->save();

    return response()->json([
        'success' => true,
        'message' => __('Status updated successfully.'),
    ]);
}


public function store(Request $request)
{
    // Validate request
    $validated = $request->validate([
        'student_id' => 'required|exists:students,id',
        'course_id' => 'required|exists:courses,id',
        'payment_method' => 'required|in:wallet_transfer,pay_in_center,contact_with_support',
        'is_active' => 'nullable|boolean',
    ]);

    // Set default for is_active if not provided (checkbox unchecked)
    $validated['is_active'] = $request->has('is_active') ? (bool) $validated['is_active'] : false;

    // Create Enrollment
    $enrollment = Enrollment::create([
        'student_id' => $validated['student_id'],
        'course_id' => $validated['course_id'],
        'payment_type' => $validated['payment_method'],
        'is_active' => $validated['is_active'],
        // Add other fields if necessary
    ]);

    $course = Course::findOrFail($enrollment->course_id);


}



public function update(Request $request, string $id)
{
    // Find the enrollment
    $enrollment = Enrollment::findOrFail($id);

    // Validate request (note: use 'nullable' here if fields may be omitted or null)
    $validated = $request->validate([
        'student_id' => 'nullable|exists:students,id',
        'course_id' => 'nullable|exists:courses,id',
        'payment_method' => 'nullable|in:wallet_transfer,pay_in_center,contact_with_support',
        'is_active' => 'nullable|boolean',
    ]);

    // Use old values if new values are null
    $validated['student_id'] = $validated['student_id'] ?? $enrollment->student_id;
    $validated['course_id'] = $validated['course_id'] ?? $enrollment->course_id;
    $validated['payment_method'] = $validated['payment_method'] ?? $enrollment->payment_type;

    // Update the enrollment
    $enrollment->update([
        'student_id' => $validated['student_id'],
        'course_id' => $validated['course_id'],
        'payment_type' => $validated['payment_method'],
        'is_active' => $validated['is_active']??false,
    ]);

    return response()->json([
        'success' => true,
        'message' => __('Enrollment updated successfully.'),
    ]);
}


    /**
     * Remove the specified resource from storage.
     */
public function destroy($id)
{
        $this->authorize('delete_enrollments'); // or a more specific ability like 'view_enrollments'

    try {
        $enrollment = Enrollment::findOrFail($id);
        $enrollment->delete();

        return response()->json([
            'success' => true,
            'message' => __('Enrollment deleted successfully.'),
        ]);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => __('Enrollment not found.'),
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => __('Failed to delete enrollment.'),
            'error' => $e->getMessage(),
        ], 500);
    }
}


    public function deleteSelected(Request $request)
    {
        $this->authorize('delete_enrollments'); // or a more specific ability like 'view_enrollments'

        Enrollment::whereIn('id', $request->selected_items_ids)->delete();
        return response(["selected Enrollment deleted successfully"]);
    }

    public function restoreSelected(Request $request)
    {
        $this->authorize('delete_enrollments'); // or a more specific ability like 'view_enrollments'
        Enrollment::withTrashed()->whereIn('id', $request->selected_items_ids)->restore();

        return response(["selected Enrollment restored successfully"]);
    }
}

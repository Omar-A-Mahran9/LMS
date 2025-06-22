<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookOrder;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

public function index()
{
    $totalCourses = Course::count();
    $totalBooks = Book::count();
    $totalStudents = Student::count();
    $totalBookings = DB::table('course_student')->count();
    $totalBookOrders = BookOrder::count();

    // Approved enrollments
    $enrollments = [
        'pending' => DB::table('course_student')->where('status', 'pending')->count(),
        'approved' => DB::table('course_student')->where('status', 'approved')->count(),
        'rejected' => DB::table('course_student')->where('status', 'rejected')->count(),
    ];

    // Book orders by status
    $bookOrderStats = [
        'pending' => BookOrder::where('status', 'pending')->count(),
        'approved' => BookOrder::where('status', 'approved')->count(),
        'rejected' => BookOrder::where('status', 'rejected')->count(),
    ];

    // Monthly enrollments (6 months)
    $monthlyEarnings = DB::table('course_student')
        ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw("COUNT(*) as total"))
        ->where('status', 'approved')
        ->groupBy('month')
        ->orderByDesc('month')
        ->limit(6)
        ->get();

    // Top 5 Courses by enrollment
    $topCourses = Course::withCount(['students as approved_students_count' => function ($query) {
            $query->where('course_student.status', 'approved');
        }])
        ->orderByDesc('approved_students_count')
        ->limit(5)
        ->get();

        $totalEarnings = DB::table('course_student')
    ->join('courses', 'course_student.course_id', '=', 'courses.id')
    ->where('course_student.status', 'approved')
    ->sum(DB::raw('courses.price'));
$studentByCategory = Student::select('categories.name_ar as category', DB::raw('COUNT(*) as total'))
    ->leftJoin('categories', 'students.category_id', '=', 'categories.id')
    ->groupBy('category')
    ->get();

 
    $courseContentStats = [
    'class_count' =>  Course::where('is_class', true)->count(),
    'section_count' =>  Course::where('is_class', false)->count(),
];

$booksStatus = Book::select('is_active', DB::raw('COUNT(*) as total'))
    ->groupBy('is_active')
    ->get();

    return view('welcome', compact(
                'totalEarnings',
        'studentByCategory',
        'courseContentStats',
        'booksStatus',

        'totalCourses',
        'totalBooks',
        'totalStudents',
        'totalBookings',
        'totalBookOrders',
        'enrollments',
        'bookOrderStats',
        'monthlyEarnings',
        'topCourses'
    ));
}




}

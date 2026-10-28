<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookOrder;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

/**
 * Numbers of the dashboard home page: KPI cards, status cards and charts.
 * One aggregate query per block, so the cards and the charts always agree.
 */
class DashboardAnalytics
{
    /** Statuses of course_student.status and book_orders.status (enum in both migrations) */
    public const STATUSES = ['pending', 'approved', 'rejected'];

    public function summary(): array
    {
        $courses = $this->courses();
        $books = $this->books();
        $bookOrders = $this->statusCounts(BookOrder::query());
        // Enrollment soft-deletes course_student rows: removed subscriptions are not counted
        $enrollments = $this->statusCounts(Enrollment::query());
        $studentsByCategory = $this->studentsByCategory();

        return [
            'kpis' => [
                'courses' => $courses['total'],
                'students' => array_sum(array_column($studentsByCategory, 'total')),
                'books' => $books['total'],
                'book_orders' => $bookOrders['total'],
                'enrollments' => $enrollments['total'],
            ],
            'courses' => $courses,
            'books' => $books,
            'book_orders' => $bookOrders,
            'enrollments' => $enrollments,
            'students_by_category' => $studentsByCategory,
            'content' => [
                'classes' => $this->activeSplit(CourseClass::query()),
                'sections' => $this->activeSplit(Section::query()),
            ],
        ];
    }

    /** total / active / inactive courses (both kinds: full courses and month / class courses) */
    private function courses(): array
    {
        return $this->activeSplit(Course::query());
    }

    private function books(): array
    {
        return $this->activeSplit(Book::query());
    }

    /**
     * Counts per status (every known status present, 0 when empty) + total + percentages.
     * The statuses are an enum, so the total is exactly the sum of the three.
     */
    private function statusCounts($query): array
    {
        $rows = $query->toBase()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = [];
        foreach (self::STATUSES as $status) {
            $counts[$status] = (int) ($rows[$status] ?? 0);
        }
        $total = array_sum($counts);

        return [
            'counts' => $counts,
            'total' => $total,
            'percentages' => array_map(fn ($count) => self::percent($count, $total), $counts),
        ];
    }

    /** total / active / inactive from the is_active column, in one query */
    private function activeSplit($query): array
    {
        $row = $query->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END), 0) as active')
            ->first();

        $total = (int) ($row->total ?? 0);
        $active = (int) ($row->active ?? 0);

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'active_percentage' => self::percent($active, $total),
            'inactive_percentage' => self::percent($total - $active, $total),
        ];
    }

    /**
     * Students per grade, biggest first. Every student is counted once: students without a grade
     * (or whose grade row is gone) go to "Uncategorized", a soft-deleted grade keeps its name.
     */
    private function studentsByCategory(): array
    {
        $nameColumn = app()->getLocale() === 'ar' ? 'name_ar' : 'name_en';

        $rows = Student::withoutGlobalScopes()->toBase()
            ->leftJoin('categories', 'categories.id', '=', 'students.category_id')
            ->select(
                'categories.id as category_id',
                "categories.{$nameColumn} as name",
                'categories.deleted_at as deleted_at',
                DB::raw('COUNT(students.id) as total')
            )
            ->groupBy('categories.id', "categories.{$nameColumn}", 'categories.deleted_at')
            ->get();

        $grandTotal = (int) $rows->sum('total');

        return $rows
            ->map(fn ($row) => [
                'category_id' => $row->category_id,
                'label' => $row->category_id
                    ? ($row->deleted_at ? $row->name . ' (' . __('Deleted') . ')' : $row->name)
                    : __('Uncategorized'),
                'total' => (int) $row->total,
                'percentage' => self::percent((int) $row->total, $grandTotal),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    public static function percent(int $part, int $total): float
    {
        return $total > 0 ? round($part / $total * 100, 1) : 0.0;
    }
}

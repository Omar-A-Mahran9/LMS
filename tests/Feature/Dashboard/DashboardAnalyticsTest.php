<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use App\Models\Book;
use App\Models\BookOrder;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Student;
use App\Services\DashboardAnalytics;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Api\ApiTestCase;

class DashboardAnalyticsTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // RoleSeeder expects admins 1 and 2 (like production)
        foreach ([1, 2] as $i) {
            Admin::create(['name' => "Seeded {$i}", 'email' => "seeded{$i}@example.test", 'password' => 'secret123']);
        }
        $this->seed(RoleSeeder::class);
    }

    private function admin(bool $superAdmin): Admin
    {
        $admin = Admin::create([
            'name' => $superAdmin ? 'Super' : 'Nobody',
            'email' => uniqid('admin') . '@example.test',
            'password' => 'secret123',
        ]);
        if ($superAdmin) {
            DB::table('admin_role')->insert(['role_id' => 1, 'admin_id' => $admin->id]);
        }

        return $admin;
    }

    private function makeBook(bool $active): Book
    {
        return Book::create(['title_en' => 'Book', 'title_ar' => 'كتاب', 'price' => 50, 'is_active' => $active]);
    }

    private function order(Book $book, string $status): void
    {
        BookOrder::create([
            'name' => 'Buyer', 'email' => 'buyer@example.test', 'phone' => '0100', 'address' => 'Cairo',
            'book_id' => $book->id, 'quantity' => 1, 'payment_type' => 'pay_in_center',
            'status' => $status, 'total_cost' => 50,
        ]);
    }

    public function test_empty_platform_gives_zeros_everywhere(): void
    {
        $stats = app(DashboardAnalytics::class)->summary();

        $this->assertSame(['courses' => 0, 'students' => 0, 'books' => 0, 'book_orders' => 0, 'enrollments' => 0], $stats['kpis']);
        $this->assertSame(['pending' => 0, 'approved' => 0, 'rejected' => 0], $stats['enrollments']['counts']);
        $this->assertSame(['pending' => 0.0, 'approved' => 0.0, 'rejected' => 0.0], $stats['book_orders']['percentages']);
        $this->assertSame([], $stats['students_by_category']);
        $this->assertSame(0, $stats['content']['classes']['total']);
        $this->assertSame(0.0, $stats['books']['active_percentage']);
    }

    public function test_status_counts_percentages_and_totals_agree(): void
    {
        $course = $this->makeCourse();
        $inactiveCourse = $this->makeCourse(['is_active' => 0]);
        $students = collect(range(1, 4))->map(fn () => $this->makeStudent());

        $this->enroll($students[0], $course, 'approved');
        $this->enroll($students[1], $course, 'approved');
        $this->enroll($students[2], $course, 'pending');
        $this->enroll($students[3], $inactiveCourse, 'rejected');
        // A removed subscription (soft-deleted) must not be counted
        $this->enroll($students[3], $course, 'approved');
        Enrollment::where('student_id', $students[3]->id)->where('course_id', $course->id)->first()->delete();

        $activeBook = $this->makeBook(true);
        $this->makeBook(true);
        $this->makeBook(false);
        foreach (['pending', 'pending', 'approved', 'rejected'] as $status) {
            $this->order($activeBook, $status);
        }

        $stats = app(DashboardAnalytics::class)->summary();

        $this->assertSame(['pending' => 1, 'approved' => 2, 'rejected' => 1], $stats['enrollments']['counts']);
        $this->assertSame(4, $stats['enrollments']['total']);
        $this->assertSame(4, $stats['kpis']['enrollments']);
        $this->assertSame(['pending' => 25.0, 'approved' => 50.0, 'rejected' => 25.0], $stats['enrollments']['percentages']);

        $this->assertSame(['pending' => 2, 'approved' => 1, 'rejected' => 1], $stats['book_orders']['counts']);
        $this->assertSame(4, $stats['kpis']['book_orders']);
        $this->assertSame(50.0, $stats['book_orders']['percentages']['pending']);

        $this->assertSame(3, $stats['kpis']['books']);
        $this->assertSame(['total' => 3, 'active' => 2, 'inactive' => 1, 'active_percentage' => 66.7, 'inactive_percentage' => 33.3], $stats['books']);

        $this->assertSame(2, $stats['kpis']['courses']);
        $this->assertSame(1, $stats['courses']['active']);
        $this->assertSame(4, $stats['kpis']['students']);
    }

    public function test_students_by_category_counts_each_student_once_with_readable_labels(): void
    {
        app()->setLocale('en');
        $grade = $this->makeCategory();
        $removedGrade = $this->makeCategory();
        $this->makeStudent(['category_id' => $grade->id]);
        $this->makeStudent(['category_id' => $grade->id]);
        $this->makeStudent(['category_id' => $grade->id]);
        $this->makeStudent(['category_id' => $removedGrade->id]);
        $this->makeStudent(['category_id' => null]);
        // Blocked students are still students of the platform
        $this->makeStudent(['category_id' => null, 'block_flag' => 1]);
        $removedGrade->delete(); // soft delete: the students keep the grade id

        $rows = collect(app(DashboardAnalytics::class)->summary()['students_by_category']);

        $this->assertSame(Student::count(), $rows->sum('total'));
        $this->assertSame([3, 2, 1], $rows->pluck('total')->all()); // biggest first
        $this->assertSame($grade->name_en, $rows[0]['label']);
        $this->assertSame(50.0, $rows[0]['percentage']);
        $this->assertSame('Uncategorized', $rows->firstWhere('category_id', null)['label']);
        $this->assertSame($removedGrade->name_en . ' (Deleted)', $rows->firstWhere('category_id', $removedGrade->id)['label']);
    }

    public function test_content_counts_real_classes_and_sections(): void
    {
        $course = $this->makeCourse(['is_class' => 0]);
        $this->makeClass($course);
        $this->makeClass($course);
        $this->makeClass($course, ['is_active' => 0]);
        Section::create(['course_id' => $course->id, 'title_en' => 'S', 'title_ar' => 'ق', 'is_active' => 1]);

        $content = app(DashboardAnalytics::class)->summary()['content'];

        $this->assertSame(['total' => 3, 'active' => 2, 'inactive' => 1], array_intersect_key($content['classes'], array_flip(['total', 'active', 'inactive'])));
        $this->assertSame(1, $content['sections']['total']);
        $this->assertSame(1, $content['sections']['active']);
    }

    public function test_dashboard_page_shows_the_charts_and_numbers_to_admins_with_the_permission(): void
    {
        $this->actingAs($this->admin(true), 'admin');
        $this->makeStudent();
        $this->order($this->makeBook(true), 'approved');

        $response = $this->get('/dashboard')->assertOk();

        foreach (['studentsCategoryChart', 'booksStatusChart', 'bookOrdersChart', 'enrollmentsChart', 'courseContentChart'] as $id) {
            $response->assertSee("id=\"{$id}\"", false);
        }
        $response->assertSee('"students_by_category"', false);
        $response->assertViewHas('stats', fn ($stats) => $stats['kpis']['students'] === 1 && $stats['kpis']['book_orders'] === 1);
        // ApexCharts comes from the layout bundle only
        $response->assertDontSee('cdn.jsdelivr.net/npm/apexcharts', false);
    }

    public function test_dashboard_page_renders_with_no_data(): void
    {
        $this->actingAs($this->admin(true), 'admin');

        $this->get('/dashboard')->assertOk()->assertSee('id="enrollmentsChart"', false);
    }

    public function test_admin_without_the_permission_gets_the_welcome_screen_and_no_numbers(): void
    {
        $this->actingAs($this->admin(false), 'admin');
        $this->makeStudent();
        $this->order($this->makeBook(true), 'pending');

        $response = $this->get('/dashboard')->assertOk();

        $response->assertViewHas('stats', null);
        $response->assertDontSee('studentsCategoryChart', false);
        $response->assertDontSee('students_by_category', false);
        $response->assertDontSee('book_orders', false);
    }
}

<?php

namespace Tests\Feature\Dashboard;

use App\Models\Ability;
use App\Models\Admin;
use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\Feature\Api\ApiTestCase;

class DashboardAccessTest extends ApiTestCase
{
    // Pages any logged-in admin may open (no ability checked by design)
    private const OPEN_TO_ALL_ADMINS = [
        'dashboard', 'dashboard/profile-info', 'dashboard/trash/{modelName?}',
    ];

    private function admin(bool $withAllAbilities): Admin
    {
        $admin = Admin::create([
            'name' => $withAllAbilities ? 'Super' : 'Nobody',
            'email' => uniqid('admin') . '@example.test',
            'password' => 'secret123',
        ]);

        if ($withAllAbilities) {
            // RoleSeeder gives role 1 (super admin) every ability
            DB::table('admin_role')->insert(['role_id' => 1, 'admin_id' => $admin->id]);
        }

        return $admin;
    }

    /** GET dashboard pages that need no route parameter */
    private function simplePages(): array
    {
        return collect(Route::getRoutes()->getRoutes())
            ->filter(fn (RoutingRoute $r) => str_starts_with($r->uri(), 'dashboard')
                && in_array('GET', $r->methods())
                && !preg_match('/\{(?!modelName\?)[^}]+\}/', $r->uri())
                && !str_contains($r->uri(), 'restore-selected')
                && !str_contains($r->uri(), 'blocked-selected')
                && !str_contains($r->uri(), 'export/pdf'))
            ->map(fn (RoutingRoute $r) => $r->uri())
            ->unique()->values()->all();
    }

    protected function setUp(): void
    {
        parent::setUp();

        // RoleSeeder runs after the admin seeder in production: it expects admins 1 and 2
        foreach ([1, 2] as $i) {
            Admin::create(['name' => "Seeded {$i}", 'email' => "seeded{$i}@example.test", 'password' => 'secret123']);
        }
        $this->seed(RoleSeeder::class);

        // The dashboard home groups by month with MySQL's DATE_FORMAT: give sqlite an equivalent
        DB::connection()->getPdo()->sqliteCreateFunction('DATE_FORMAT', fn ($date, $format) => $date
            ? date(strtr($format, ['%Y' => 'Y', '%m' => 'm', '%d' => 'd', '%H' => 'H', '%i' => 'i']), strtotime($date))
            : null, 2);
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/dashboard')->assertRedirect(route('admin.login-form'));
        $this->get('/dashboard/students')->assertRedirect(route('admin.login-form'));
    }

    public function test_students_tokens_do_not_open_the_dashboard(): void
    {
        $this->actingAsStudent($this->makeStudent());

        $this->get('/dashboard/students')->assertRedirect(route('admin.login-form'));
    }

    public function test_every_dashboard_page_loads_for_a_full_admin(): void
    {
        $this->actingAs($this->admin(true), 'admin');
        $errors = [];

        foreach ($this->simplePages() as $uri) {
            $status = $this->get('/' . str_replace('{modelName?}', '', $uri))->status();
            if ($status >= 500 || $status === 403) {
                $errors[] = "{$uri} => {$status}";
            }
        }

        $this->assertSame([], $errors);
    }

    public function test_admin_without_abilities_is_refused_everywhere_but_the_home_page(): void
    {
        $this->actingAs($this->admin(false), 'admin');
        $open = [];

        foreach ($this->simplePages() as $uri) {
            if (in_array($uri, self::OPEN_TO_ALL_ADMINS)) {
                continue;
            }
            $status = $this->get('/' . str_replace('{modelName?}', '', $uri))->status();
            if ($status < 400) {
                $open[] = "{$uri} => {$status}";
            }
        }

        $this->assertSame([], $open);
    }

    public function test_admin_without_abilities_cannot_write_through_direct_requests(): void
    {
        $this->actingAs($this->admin(false), 'admin');
        $student = $this->makeStudent();

        $this->get("/dashboard/students/blocking/{$student->id}")->assertForbidden();
        $this->postJson('/dashboard/categories', ['name_ar' => 'x', 'name_en' => 'x'])->assertForbidden();
        $this->deleteJson("/dashboard/students/{$student->id}")->assertForbidden();
        $this->postJson('/dashboard/settings/roles', ['name_ar' => 'x', 'name_en' => 'x'])->assertForbidden();
        $this->assertSame(0, (int) $student->fresh()->block_flag);
    }

    public function test_blocking_a_student_ends_their_sessions(): void
    {
        $this->actingAs($this->admin(true), 'admin');
        $student = $this->makeStudent();
        $student->createToken('phone');

        $this->get("/dashboard/students/blocking/{$student->id}")->assertOk();

        $this->assertSame(1, (int) $student->fresh()->block_flag);
        $this->assertSame(0, $student->tokens()->count());
    }
}

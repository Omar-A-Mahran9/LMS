<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use App\Models\ClassAccessCode;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Api\ApiTestCase;

class AccessCodeDashboardTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // RoleSeeder expects admins 1 and 2 and gives role 1 (super admin) every ability
        foreach ([1, 2] as $i) {
            Admin::create(['name' => "Seeded {$i}", 'email' => "seeded{$i}@example.test", 'password' => 'secret123']);
        }
        $this->seed(RoleSeeder::class);

        $admin = Admin::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'secret123']);
        DB::table('admin_role')->insert(['role_id' => 1, 'admin_id' => $admin->id]);
        $this->actingAs($admin, 'admin');
    }

    private function code(int $classId, array $attributes = []): ClassAccessCode
    {
        return ClassAccessCode::create(array_merge([
            'class_id' => $classId,
            'code' => strtoupper(uniqid()),
            'single_use' => true,
            'used_count' => 0,
            'is_active' => true,
        ], $attributes));
    }

    private function table(array $params = []): \Illuminate\Testing\TestResponse
    {
        return $this->getJson('/dashboard/generateCode?' . http_build_query($params + [
            'draw' => 1, 'start' => 0, 'length' => 50, 'search' => ['value' => ''],
        ]), ['X-Requested-With' => 'XMLHttpRequest']);
    }

    public function test_generates_a_batch_of_unique_readable_codes(): void
    {
        $class = $this->makeClass($this->makeCourse());

        $response = $this->postJson('/dashboard/generateCode', [
            'class_id' => $class->id, 'code_count' => 25, 'single_use' => 1, 'usage_limit' => 5, 'is_active' => 1,
        ])->assertOk()->assertJsonPath('count', 25);

        $this->assertStringContainsString('batch_at=', $response->json('export_url'));
        $codes = ClassAccessCode::pluck('code');
        $this->assertCount(25, $codes->unique());
        $codes->each(fn ($code) => $this->assertMatchesRegularExpression('/^[A-HJKMNP-Z2-9]{10}$/', $code));
        // a single-use code has no separate limit
        $this->assertNull(ClassAccessCode::first()->usage_limit);
    }

    public function test_batch_size_is_capped(): void
    {
        $class = $this->makeClass($this->makeCourse());

        $this->postJson('/dashboard/generateCode', ['class_id' => $class->id, 'code_count' => 1001])
            ->assertStatus(422)->assertJsonValidationErrors('code_count');
    }

    public function test_edit_can_switch_a_code_off(): void
    {
        $class = $this->makeClass($this->makeCourse());
        $code = $this->code($class->id);

        // the form sends 0 for a switch that is off
        $this->putJson("/dashboard/generateCode/{$code->id}", [
            'class_id' => $class->id, 'code' => $code->code, 'is_active' => 0, 'single_use' => 0, 'usage_limit' => 3,
        ])->assertOk();

        $code->refresh();
        $this->assertFalse($code->is_active);
        $this->assertFalse($code->single_use);
        $this->assertSame(3, (int) $code->usage_limit);
    }

    public function test_table_filters_by_status_and_class_and_counts_each_status(): void
    {
        $classA = $this->makeClass($this->makeCourse());
        $classB = $this->makeClass($this->makeCourse());

        $this->code($classA->id);                                                   // unused
        $this->code($classA->id, ['single_use' => false, 'usage_limit' => 3, 'used_count' => 1]); // used
        $this->code($classA->id, ['used_count' => 1]);                              // exhausted
        $this->code($classA->id, ['is_active' => false]);                           // inactive
        $this->code($classB->id);

        $this->table(['class_id' => $classA->id])
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 4)
            ->assertJsonPath('stats', ['all' => 4, 'unused' => 1, 'used' => 1, 'exhausted' => 1, 'inactive' => 1]);

        $rows = $this->table(['class_id' => $classA->id, 'status' => 'exhausted'])->json('data');
        $this->assertCount(1, $rows);
        $this->assertSame('exhausted', $rows[0]['status']);

        $this->table(['status' => 'unused'])->assertJsonPath('recordsFiltered', 2);
    }

    public function test_delete_selected(): void
    {
        $class = $this->makeClass($this->makeCourse());
        $ids = [$this->code($class->id)->id, $this->code($class->id)->id];
        $this->code($class->id);

        $this->deleteJson('/dashboard/generateCode/delete-selected', ['selected_items_ids' => $ids])->assertOk();

        $this->assertDatabaseCount('class_access_codes', 1);
    }

    public function test_pdf_export_follows_the_filters(): void
    {
        $class = $this->makeClass($this->makeCourse());
        $this->code($class->id);

        foreach (['table', 'cards'] as $layout) {
            $this->get("/dashboard/generate-codes/export/pdf?class_id={$class->id}&layout={$layout}")
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf');
        }
    }
}

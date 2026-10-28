<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use App\Models\Bundle;
use App\Models\BundleStudent;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Api\ApiTestCase;

class BundleDashboardTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // RoleSeeder expects admins 1 and 2 and gives role 1 (super admin) every ability
        foreach ([1, 2] as $i) {
            Admin::create(['name' => "Seeded {$i}", 'email' => "seeded{$i}@example.test", 'password' => 'secret123']);
        }
        $this->seed(RoleSeeder::class);

        $admin = Admin::create(['name' => 'Admin', 'email' => uniqid('admin') . '@example.test', 'password' => 'secret123']);
        DB::table('admin_role')->insert(['role_id' => 1, 'admin_id' => $admin->id]);
        $this->actingAs($admin, 'admin');

        Storage::fake('public');
    }

    private function payload(array $classIds, array $overrides = []): array
    {
        return array_merge([
            'title_ar' => 'باقة الترم',
            'title_en' => 'Term bundle',
            'description_ar' => 'وصف',
            'description_en' => 'desc',
            'image' => UploadedFile::fake()->image('bundle.jpg'),
            'classes' => $classIds,
            'price' => 300,
            'discount_price' => 250,
            'code_count' => 3,
            'single_use' => 1,
            'is_active' => 1,
        ], $overrides);
    }

    /** DataTables request the bundles page sends. */
    private function tableRequest(): array
    {
        return [
            'draw' => 1, 'start' => 0, 'length' => 10,
            'search' => ['value' => ''],
            'columns' => collect(['id', 'image_url', 'title_ar', 'classes_count', 'codes_count', 'is_active', 'created_at', null])
                ->map(fn($c) => ['data' => $c, 'name' => '', 'search' => ['value' => '']])->all(),
        ];
    }

    public function test_full_bundle_workflow_in_the_dashboard(): void
    {
        $class = $this->makeClass($this->makeCourse());

        $this->post('/dashboard/bundles', $this->payload([$class->id]), ['Accept' => 'application/json'])->assertOk();
        $bundle = Bundle::firstOrFail();
        $this->assertSame(3, $bundle->codes()->count());
        $this->assertEquals(250, $bundle->final_price);

        // the table loads with the classes and the code settings used by the edit form
        $this->getJson('/dashboard/bundles?' . http_build_query($this->tableRequest()), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('data.0.classes.0.id', $class->id)
            ->assertJsonCount(3, 'data.0.codes');

        // edit: price saved, 2 more codes, the old ones kept
        $this->put("/dashboard/bundles/{$bundle->id}", $this->payload([$class->id], [
            'image' => null, 'price' => 400, 'discount_price' => null, 'add_codes' => 2,
        ]), ['Accept' => 'application/json'])->assertOk();
        $bundle->refresh();
        $this->assertEquals(400, $bundle->price);
        $this->assertNull($bundle->discount_price);
        $this->assertSame(5, $bundle->codes()->count());

        // the bundle page shows who activated it
        $student = $this->makeStudent(['first_name' => 'Mona']);
        BundleStudent::create(['bundle_id' => $bundle->id, 'student_id' => $student->id, 'access_code_id' => $bundle->codes()->first()->id, 'used_at' => now()]);
        $this->get("/dashboard/bundles/{$bundle->id}")->assertOk()->assertSee('Mona');
        $this->get('/dashboard/codes/' . $bundle->codes()->first()->id)->assertOk()->assertSee('Mona');

        $this->deleteJson('/dashboard/bundles/delete-selected', ['selected_items_ids' => [$bundle->id]])->assertOk();
        $this->assertDatabaseCount('bundles', 0);
        $this->assertDatabaseCount('bundle_access_codes', 0);
    }

    public function test_discount_cannot_be_more_than_the_price(): void
    {
        $class = $this->makeClass($this->makeCourse());

        $this->post('/dashboard/bundles', $this->payload([$class->id], ['discount_price' => 500]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('discount_price');
    }
}

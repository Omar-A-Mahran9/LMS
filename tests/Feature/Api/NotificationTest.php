<?php

namespace Tests\Feature\Api;

use App\Models\StudentNotification;
use App\Services\StudentNotifier;

class NotificationTest extends ApiTestCase
{
    private function send(string $target, array $ids = []): StudentNotification
    {
        return StudentNotifier::send([
            'type' => 'message',
            'title_ar' => 'رسالة',
            'title_en' => 'Message',
            'body_ar' => 'نص',
            'body_en' => 'Body',
        ], $target, $ids);
    }

    public function test_each_student_only_sees_the_notifications_meant_for_them(): void
    {
        $alice = $this->makeStudent();
        $bob = $this->makeStudent();

        $forAlice = $this->send(StudentNotification::TARGET_STUDENTS, [$alice->id]);
        $forBobsGrade = $this->send(StudentNotification::TARGET_CATEGORIES, [$bob->category_id]);
        $forAll = $this->send(StudentNotification::TARGET_ALL);

        $this->actingAsStudent($alice);
        $ids = collect($this->getJson('/api/notifications')->assertOk()->json('data.notifications'))->pluck('id');
        $this->assertEqualsCanonicalizing([$forAlice->id, $forAll->id], $ids->all());

        // Bob can't mark (or even find) Alice's private notification
        $this->actingAsStudent($bob);
        $this->postJson("/api/notifications/{$forAlice->id}/read")->assertNotFound();
        $this->getJson('/api/notifications/unread-count')->assertOk()->assertJsonPath('data.unread_count', 2);
        $this->postJson("/api/notifications/{$forBobsGrade->id}/read")->assertOk()->assertJsonPath('data.unread_count', 1);
        $this->postJson('/api/notifications/read-all')->assertOk();
        $this->getJson('/api/notifications/unread-count')->assertJsonPath('data.unread_count', 0);
    }

    public function test_notification_is_saved_even_when_pusher_is_unreachable(): void
    {
        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.key' => 'key',
            'broadcasting.connections.pusher.secret' => 'secret',
            'broadcasting.connections.pusher.app_id' => '1',
            'broadcasting.connections.pusher.options.host' => '127.0.0.1',
            'broadcasting.connections.pusher.options.port' => 1,
            'broadcasting.connections.pusher.options.scheme' => 'http',
            'broadcasting.connections.pusher.options.useTLS' => false,
        ]);
        $student = $this->makeStudent();

        $notification = $this->send(StudentNotification::TARGET_STUDENTS, [$student->id]);

        $this->assertDatabaseHas('student_notifications', ['id' => $notification->id]);
        $this->actingAsStudent($student);
        $this->getJson('/api/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.unread_count', 1)
            ->assertJsonPath('data.realtime.enabled', true);
    }

    public function test_guests_cannot_read_notifications(): void
    {
        $this->getJson('/api/notifications')->assertUnauthorized();
    }
}

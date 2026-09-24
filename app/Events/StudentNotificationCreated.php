<?php

namespace App\Events;

use App\Models\StudentNotification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * Tells the website instantly that a notification exists for these students.
 * Only a signal (id + type) is broadcast, never the text: the site then loads it through the
 * authenticated API, so the public channels don't expose anything.
 */
class StudentNotificationCreated implements ShouldBroadcastNow
{
    public function __construct(public StudentNotification $notification)
    {
    }

    /** Channel names shared with NotificationController::realtimeConfig(). */
    public static function channelFor(string $target, ?int $id = null): string
    {
        return match ($target) {
            StudentNotification::TARGET_CATEGORIES => "notifications.category.{$id}",
            StudentNotification::TARGET_STUDENTS   => "notifications.student.{$id}",
            default                                => 'notifications.all',
        };
    }

    public function broadcastOn(): array
    {
        $n = $this->notification;

        return match ($n->target) {
            StudentNotification::TARGET_CATEGORIES => $n->categories()->pluck('categories.id')
                ->map(fn($id) => new Channel(self::channelFor($n->target, $id)))->all(),
            StudentNotification::TARGET_STUDENTS   => $n->students()->pluck('students.id')
                ->map(fn($id) => new Channel(self::channelFor($n->target, $id)))->all(),
            default                                => [new Channel(self::channelFor($n->target))],
        };
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    public function broadcastWith(): array
    {
        return ['id' => $this->notification->id, 'type' => $this->notification->type];
    }
}

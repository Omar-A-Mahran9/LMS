<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\StudentNotificationCreated;
use App\Models\StudentNotification;
use App\Services\StudentNotifier;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $student = auth('api')->user();

        $notifications = StudentNotification::forStudent($student)
            ->withReadState($student)
            ->latest('id')
            ->paginate(min((int) $request->get('per_page', 15), 50));

        return $this->success('', [
            'unread_count'  => $this->unread($student),
            'notifications' => collect($notifications->items())->map(fn($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'body'       => $n->body,
                'link'       => $n->link,
                'is_read'    => (bool) $n->is_read,
                'created_at' => $n->created_at?->toIso8601String(),
            ]),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'total'        => $notifications->total(),
            ],
        ]);
    }

    public function unreadCount()
    {
        $student = auth('api')->user();

        return $this->success('', [
            'unread_count' => $this->unread($student),
            'realtime'     => $this->realtimeConfig($student),
        ]);
    }

    /**
     * What the site needs to listen for new notifications live (Pusher public key + this student's
     * channels). The key is public by design; the secret never leaves the server.
     */
    private function realtimeConfig($student): array
    {
        if (!StudentNotifier::realtimeEnabled()) {
            return ['enabled' => false];
        }

        $channels = [StudentNotificationCreated::channelFor(StudentNotification::TARGET_ALL)];
        if ($student->category_id) {
            $channels[] = StudentNotificationCreated::channelFor(StudentNotification::TARGET_CATEGORIES, $student->category_id);
        }
        $channels[] = StudentNotificationCreated::channelFor(StudentNotification::TARGET_STUDENTS, $student->id);

        return [
            'enabled'  => true,
            'key'      => config('broadcasting.connections.pusher.key'),
            'cluster'  => config('broadcasting.connections.pusher.options.cluster'),
            'event'    => 'notification.created',
            'channels' => $channels,
        ];
    }

    public function markAsRead($id)
    {
        $student = auth('api')->user();

        $notification = StudentNotification::forStudent($student)->findOrFail($id);
        $notification->readers()->syncWithoutDetaching([$student->id => ['read_at' => now()]]);

        return $this->success('', ['unread_count' => $this->unread($student)]);
    }

    public function markAllAsRead()
    {
        $student = auth('api')->user();

        $ids = StudentNotification::forStudent($student)->unreadFor($student)->pluck('id');
        $student->readNotifications()->syncWithoutDetaching(
            $ids->mapWithKeys(fn($id) => [$id => ['read_at' => now()]])->all()
        );

        return $this->success('', ['unread_count' => 0]);
    }

    private function unread($student): int
    {
        return StudentNotification::forStudent($student)->unreadFor($student)->count();
    }
}

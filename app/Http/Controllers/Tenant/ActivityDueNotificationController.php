<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\DismissActivityDuePopup;
use App\Actions\RecordActivityDueNotification;
use App\Http\Controllers\Controller;
use App\Notifications\ActivityDueNotification;
use App\Queries\DueActivitiesForUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Validation\Rule;

class ActivityDueNotificationController extends Controller
{
    public function index(
        Request $request,
        DueActivitiesForUser $dueActivitiesForUser,
        RecordActivityDueNotification $recordActivityDueNotification,
    ): JsonResponse {
        $user = $request->user();
        $due = $dueActivitiesForUser->handle($user);

        $recordActivityDueNotification->syncForUser($user, $due['popups']);

        $notifications = $user->notifications()
            ->where('type', ActivityDueNotification::class)
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (DatabaseNotification $notification): array => $this->mapNotification($notification))
            ->values();

        return response()->json([
            'popups' => $due['popups'],
            'upcoming' => $due['upcoming'],
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()
                ->where('type', ActivityDueNotification::class)
                ->count(),
            'server_now' => now()->toIso8601String(),
        ]);
    }

    public function dismissPopup(Request $request, DismissActivityDuePopup $dismissActivityDuePopup): JsonResponse
    {
        $validated = $request->validate([
            'subject_type' => ['required', Rule::in(['scheduled_event', 'task'])],
            'subject_id' => ['required', 'integer', 'min:1'],
        ]);

        $dismissActivityDuePopup->handle(
            $request->user(),
            $validated['subject_type'],
            (int) $validated['subject_id'],
        );

        return response()->json(['dismissed' => true]);
    }

    public function markRead(Request $request, string $notification): JsonResponse
    {
        $record = $request->user()
            ->notifications()
            ->where('type', ActivityDueNotification::class)
            ->whereKey($notification)
            ->firstOrFail();

        $record->markAsRead();

        return response()->json(['read' => true]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()
            ->unreadNotifications()
            ->where('type', ActivityDueNotification::class)
            ->update(['read_at' => now()]);

        return response()->json(['read' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapNotification(DatabaseNotification $notification): array
    {
        $data = is_array($notification->data) ? $notification->data : [];

        return [
            'id' => $notification->id,
            'kind' => $data['kind'] ?? null,
            'subject_type' => $data['subject_type'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'title' => $data['title'] ?? __('Notification'),
            'body' => $data['body'] ?? '',
            'lead_id' => $data['lead_id'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
        ];
    }
}

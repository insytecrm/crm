<?php

namespace App\Actions;

use App\Enums\LeadScheduledEventStatus;
use App\Enums\TaskStatus;
use App\Models\LeadScheduledEvent;
use App\Models\LeadTask;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DismissActivityDuePopup
{
    public function __construct(
        private RecordActivityDueNotification $recordActivityDueNotification,
    ) {}

    public function handle(User $user, string $subjectType, int $subjectId): void
    {
        if ($subjectType === 'task') {
            $task = LeadTask::query()
                ->whereKey($subjectId)
                ->where('assigned_to_id', $user->id)
                ->whereIn('status', [TaskStatus::Pending, TaskStatus::InProgress])
                ->first();

            if ($task === null) {
                throw ValidationException::withMessages([
                    'subject_id' => __('Activity not found.'),
                ]);
            }

            if ($task->reminder_dismissed_at === null) {
                $task->update(['reminder_dismissed_at' => now()]);
            }

            $this->recordActivityDueNotification->syncForUser($user, [[
                'subject_type' => 'task',
                'subject_id' => $task->id,
            ]]);

            return;
        }

        if ($subjectType !== 'scheduled_event') {
            throw ValidationException::withMessages([
                'subject_type' => __('Invalid activity type.'),
            ]);
        }

        $event = LeadScheduledEvent::query()
            ->whereKey($subjectId)
            ->where('status', LeadScheduledEventStatus::Scheduled)
            ->whereHas('lead', fn ($query) => $query->where('assigned_to_id', $user->id))
            ->first();

        if ($event === null) {
            throw ValidationException::withMessages([
                'subject_id' => __('Activity not found.'),
            ]);
        }

        if ($event->reminder_dismissed_at === null) {
            $event->update(['reminder_dismissed_at' => now()]);
        }

        $this->recordActivityDueNotification->syncForUser($user, [[
            'subject_type' => 'scheduled_event',
            'subject_id' => $event->id,
        ]]);
    }
}

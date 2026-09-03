<?php

namespace App\Queries;

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\TaskStatus;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\LeadTask;
use App\Models\User;
use App\Support\LeadDrawerRedirect;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DueReminders
{
    /**
     * @return Collection<int, array{
     *     key: string,
     *     type: string,
     *     title: string,
     *     subtitle: string,
     *     remind_at: string,
     *     url: string,
     *     subject_type: string,
     *     subject_id: int,
     * }>
     */
    public function forUser(User $user): Collection
    {
        $now = now();

        $events = LeadScheduledEvent::query()
            ->with(['lead', 'property'])
            ->where('status', LeadScheduledEventStatus::Scheduled)
            ->whereNotNull('remind_at')
            ->whereNull('reminder_dismissed_at')
            ->where('remind_at', '<=', $now)
            ->whereHas('lead', function (Builder $query) use ($user): void {
                $query
                    ->where('assigned_to_id', $user->id)
                    ->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Lost]);
            })
            ->orderBy('remind_at')
            ->get()
            ->map(fn (LeadScheduledEvent $event): array => $this->mapEvent($event));

        $tasks = LeadTask::query()
            ->with('lead')
            ->where('assigned_to_id', $user->id)
            ->whereNotIn('status', [TaskStatus::Complete, TaskStatus::Cancelled])
            ->whereNotNull('remind_at')
            ->whereNull('reminder_dismissed_at')
            ->where('remind_at', '<=', $now)
            ->orderBy('remind_at')
            ->get()
            ->map(fn (LeadTask $task): array => $this->mapTask($task));

        return $events
            ->concat($tasks)
            ->sortBy('remind_at')
            ->values();
    }

    public function dismiss(User $user, string $subjectType, int $subjectId): bool
    {
        return match ($subjectType) {
            'scheduled_event' => $this->dismissEvent($user, $subjectId),
            'task' => $this->dismissTask($user, $subjectId),
            default => false,
        };
    }

    private function dismissEvent(User $user, int $subjectId): bool
    {
        $event = LeadScheduledEvent::query()
            ->with('lead')
            ->whereKey($subjectId)
            ->where('status', LeadScheduledEventStatus::Scheduled)
            ->whereNotNull('remind_at')
            ->whereNull('reminder_dismissed_at')
            ->first();

        if ($event === null || $event->lead?->assigned_to_id !== $user->id) {
            return false;
        }

        $event->update(['reminder_dismissed_at' => now()]);

        return true;
    }

    private function dismissTask(User $user, int $subjectId): bool
    {
        $task = LeadTask::query()
            ->whereKey($subjectId)
            ->where('assigned_to_id', $user->id)
            ->whereNotNull('remind_at')
            ->whereNull('reminder_dismissed_at')
            ->first();

        if ($task === null) {
            return false;
        }

        $task->update(['reminder_dismissed_at' => now()]);

        return true;
    }

    /**
     * @return array{
     *     key: string,
     *     type: string,
     *     title: string,
     *     subtitle: string,
     *     remind_at: string,
     *     url: string,
     *     subject_type: string,
     *     subject_id: int,
     * }
     */
    private function mapEvent(LeadScheduledEvent $event): array
    {
        $lead = $event->lead;
        $isFollowUp = $event->type === LeadScheduledEventType::FollowUp;
        $type = $isFollowUp ? 'follow_up' : 'site_visit';

        $subtitleParts = array_filter([
            $lead?->name,
            $event->scheduled_at?->format('M j, Y g:i A'),
            $event->property?->listLabel(),
        ]);

        return [
            'key' => 'scheduled_event:'.$event->id,
            'type' => $type,
            'title' => $isFollowUp ? __('Follow-up reminder') : __('Site visit reminder'),
            'subtitle' => implode(' · ', $subtitleParts),
            'remind_at' => $event->remind_at?->toIso8601String() ?? '',
            'url' => $this->leadUrl($lead),
            'subject_type' => 'scheduled_event',
            'subject_id' => $event->id,
        ];
    }

    /**
     * @return array{
     *     key: string,
     *     type: string,
     *     title: string,
     *     subtitle: string,
     *     remind_at: string,
     *     url: string,
     *     subject_type: string,
     *     subject_id: int,
     * }
     */
    private function mapTask(LeadTask $task): array
    {
        $subtitleParts = array_filter([
            $task->title,
            $task->lead?->name,
            $task->due_at?->format('M j, Y g:i A'),
        ]);

        return [
            'key' => 'task:'.$task->id,
            'type' => 'task',
            'title' => __('Task reminder'),
            'subtitle' => implode(' · ', $subtitleParts),
            'remind_at' => $task->remind_at?->toIso8601String() ?? '',
            'url' => $task->lead
                ? $this->leadUrl($task->lead)
                : route('tenant.tasks.index'),
            'subject_type' => 'task',
            'subject_id' => $task->id,
        ];
    }

    private function leadUrl(?Lead $lead): string
    {
        if ($lead === null) {
            return route('tenant.dashboard');
        }

        return LeadDrawerRedirect::appendLeadQuery(route('tenant.leads.index'), $lead->id);
    }
}

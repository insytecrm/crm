<?php

namespace App\Queries;

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\TaskStatus;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\LeadTask;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DueActivitiesForUser
{
    /**
     * @return array{
     *     popups: list<array<string, mixed>>,
     *     upcoming: list<array<string, mixed>>,
     * }
     */
    public function handle(User $user, ?CarbonInterface $now = null): array
    {
        $now ??= now();
        $upcomingUntil = $now->copy()->addHours(2);

        $events = LeadScheduledEvent::query()
            ->with(['lead:id,name,phone,status,assigned_to_id'])
            ->where('status', LeadScheduledEventStatus::Scheduled)
            ->where('scheduled_at', '<=', $upcomingUntil)
            ->whereHas('lead', fn ($query) => $query->where('assigned_to_id', $user->id))
            ->orderBy('scheduled_at')
            ->get();

        $tasks = LeadTask::query()
            ->with(['lead:id,name,phone,status,assigned_to_id'])
            ->whereIn('status', [TaskStatus::Pending, TaskStatus::InProgress])
            ->whereNotNull('due_at')
            ->where('due_at', '<=', $upcomingUntil)
            ->where('assigned_to_id', $user->id)
            ->orderBy('due_at')
            ->get();

        /** @var Collection<int, array<string, mixed>> $items */
        $items = $events
            ->map(fn (LeadScheduledEvent $event): array => $this->mapEvent($event, $now))
            ->concat($tasks->map(fn (LeadTask $task): array => $this->mapTask($task, $now)))
            ->sortBy('due_at')
            ->values();

        return [
            'popups' => $items
                ->filter(fn (array $item): bool => $item['is_due'] && ! $item['popup_dismissed'])
                ->values()
                ->all(),
            'upcoming' => $items
                ->filter(fn (array $item): bool => ! $item['is_due'] && ! $item['popup_dismissed'])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapEvent(LeadScheduledEvent $event, CarbonInterface $now): array
    {
        $lead = $event->lead;
        $kind = match ($event->type) {
            LeadScheduledEventType::FollowUp => 'follow_up',
            LeadScheduledEventType::SiteVisit => 'site_visit',
        };

        return [
            'kind' => $kind,
            'subject_type' => 'scheduled_event',
            'subject_id' => $event->id,
            'label' => $event->ordinalLabel(),
            'type_label' => $event->type->label(),
            'notes' => $event->notes,
            'due_at' => $event->scheduled_at?->toIso8601String(),
            'is_due' => $event->scheduled_at !== null && $event->scheduled_at->lte($now),
            'popup_dismissed' => $event->reminder_dismissed_at !== null,
            'lead' => $this->mapLead($lead),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapTask(LeadTask $task, CarbonInterface $now): array
    {
        return [
            'kind' => 'task',
            'subject_type' => 'task',
            'subject_id' => $task->id,
            'label' => $task->title,
            'type_label' => __('Task'),
            'notes' => $task->description,
            'due_at' => $task->due_at?->toIso8601String(),
            'is_due' => $task->due_at !== null && $task->due_at->lte($now),
            'popup_dismissed' => $task->reminder_dismissed_at !== null,
            'lead' => $this->mapLead($task->lead),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mapLead(?Lead $lead): ?array
    {
        if ($lead === null) {
            return null;
        }

        return [
            'id' => $lead->id,
            'name' => $lead->name,
            'phone' => $lead->phone,
            'status' => $lead->status?->label(),
            'call_url' => $lead->callUrl(),
            'can_whatsapp' => $lead->whatsAppUrl() !== null,
        ];
    }
}

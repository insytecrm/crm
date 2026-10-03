<?php

namespace App\Notifications;

use App\Enums\LeadScheduledEventType;
use App\Models\LeadScheduledEvent;
use App\Models\LeadTask;
use Illuminate\Notifications\Notification;

class ActivityDueNotification extends Notification
{
    public function __construct(
        public string $kind,
        public int $subjectId,
        public string $title,
        public string $body,
        public ?int $leadId = null,
        public ?string $scheduledAt = null,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{
     *     kind: string,
     *     subject_type: string,
     *     subject_id: int,
     *     title: string,
     *     body: string,
     *     lead_id: ?int,
     *     scheduled_at: ?string,
     * }
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->kind,
            'subject_type' => $this->subjectType(),
            'subject_id' => $this->subjectId,
            'title' => $this->title,
            'body' => $this->body,
            'lead_id' => $this->leadId,
            'scheduled_at' => $this->scheduledAt,
        ];
    }

    public function subjectType(): string
    {
        return match ($this->kind) {
            'task' => 'task',
            default => 'scheduled_event',
        };
    }

    public static function fromScheduledEvent(LeadScheduledEvent $event): self
    {
        $event->loadMissing('lead');

        $kind = match ($event->type) {
            LeadScheduledEventType::FollowUp => 'follow_up',
            LeadScheduledEventType::SiteVisit => 'site_visit',
        };

        return new self(
            kind: $kind,
            subjectId: $event->id,
            title: __(':type due', ['type' => $event->type->label()]),
            body: $event->lead?->name ?? __('Lead'),
            leadId: $event->lead_id,
            scheduledAt: $event->scheduled_at?->toIso8601String(),
        );
    }

    public static function fromTask(LeadTask $task): self
    {
        $task->loadMissing('lead');

        return new self(
            kind: 'task',
            subjectId: $task->id,
            title: __('Task due'),
            body: $task->title.($task->lead ? ' · '.$task->lead->name : ''),
            leadId: $task->lead_id,
            scheduledAt: $task->due_at?->toIso8601String(),
        );
    }
}

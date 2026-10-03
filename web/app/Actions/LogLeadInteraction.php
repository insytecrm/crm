<?php

namespace App\Actions;

use App\Enums\LeadActivityType;
use App\Enums\LeadScheduledEventType;
use App\Enums\ScheduledActivityPriority;
use App\Models\Lead;
use App\Support\LeadAutoStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LogLeadInteraction
{
    public function __construct(
        private LogLeadActivity $logLeadActivity,
        private RecordLeadScheduledEvent $recordLeadScheduledEvent,
        private LeadAutoStatus $leadAutoStatus,
    ) {}

    /**
     * @param  array{
     *     interaction_type: string,
     *     body?: ?string,
     *     next_follow_up_at?: ?string,
     *     next_follow_up_notes?: ?string,
     * }  $data
     */
    public function handle(Lead $lead, array $data): void
    {
        DB::transaction(function () use ($lead, $data): void {
            $activityType = $this->activityTypeForInteraction($data['interaction_type']);
            $body = trim((string) ($data['body'] ?? ''));

            if ($activityType === LeadActivityType::NoteAdded && $body !== '') {
                $note = $lead->notes()->create([
                    'body' => $body,
                    'user_id' => auth()->id(),
                ]);

                $this->logLeadActivity->handle(
                    $lead,
                    LeadActivityType::NoteAdded,
                    __('Note added'),
                    metadata: [
                        'note_id' => $note->id,
                        'body' => $note->body,
                    ],
                );
            } else {
                $description = $body !== '' ? $body : $activityType->label();

                $this->logLeadActivity->handle(
                    $lead,
                    $activityType,
                    $description,
                );
            }

            if (filled($data['next_follow_up_at'] ?? null)) {
                $scheduledAt = Carbon::parse($data['next_follow_up_at']);
                $notes = trim((string) ($data['next_follow_up_notes'] ?? ''));

                $lead->update([
                    'next_follow_up_at' => $scheduledAt,
                    'next_action' => __('Follow-up'),
                ]);

                $event = $this->recordLeadScheduledEvent->schedule(
                    $lead,
                    LeadScheduledEventType::FollowUp,
                    $scheduledAt,
                    $notes !== '' ? $notes : null,
                    ScheduledActivityPriority::Normal,
                );

                $this->logLeadActivity->handle(
                    $lead,
                    LeadActivityType::FollowUpScheduled,
                    __(':label scheduled for :date', [
                        'label' => $event->ordinalLabel(),
                        'date' => $scheduledAt->format('M j, Y g:i A'),
                    ]),
                    metadata: [
                        'scheduled_event_id' => $event->id,
                        'sequence_number' => $event->sequence_number,
                    ],
                );

                $this->leadAutoStatus->applyForActivity($lead->fresh() ?? $lead, LeadActivityType::FollowUpScheduled);

                return;
            }

            $this->leadAutoStatus->applyForActivity($lead->fresh() ?? $lead, $activityType);
        });
    }

    private function activityTypeForInteraction(string $interactionType): LeadActivityType
    {
        return match ($interactionType) {
            'call' => LeadActivityType::CallMade,
            'whatsapp' => LeadActivityType::WhatsAppMessage,
            'meeting' => LeadActivityType::CallMade,
            'note' => LeadActivityType::NoteAdded,
            default => LeadActivityType::CallMade,
        };
    }
}

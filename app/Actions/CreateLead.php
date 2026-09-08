<?php

namespace App\Actions;

use App\Enums\LeadActivityType;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\PlanLimitKey;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateLead
{
    public const INITIAL_CONTACT_DELAY_MINUTES = 5;

    public function __construct(
        private LogLeadActivity $logLeadActivity,
        private RecordLeadScheduledEvent $recordLeadScheduledEvent,
        private AssertPlanLimit $assertPlanLimit,
        private ApplyLeadRouting $applyLeadRouting,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $user = null): Lead
    {
        $this->assertPlanLimit->handle(PlanLimitKey::Leads);

        $user ??= auth()->user();

        return DB::transaction(function () use ($data, $user): Lead {
            $assignedToId = filled($data['assigned_to_id'] ?? null)
                ? (int) $data['assigned_to_id']
                : null;

            if ($assignedToId === null) {
                $assignedToId = $this->applyLeadRouting->handle(
                    isset($data['source']) ? (string) $data['source'] : null,
                    isset($data['sub_source']) ? (string) $data['sub_source'] : null,
                ) ?? $user?->id;
            }

            $lead = Lead::query()->create([
                ...$data,
                'status' => LeadStatus::New,
                'created_by_id' => $user?->id,
                'assigned_to_id' => $assignedToId,
                'last_activity_at' => now(),
            ]);

            $this->logLeadActivity->handle(
                $lead,
                LeadActivityType::LeadCreated,
                __('Lead created'),
                $user,
            );

            $this->scheduleInitialContact($lead, $user);

            return $lead->fresh();
        });
    }

    private function scheduleInitialContact(Lead $lead, ?User $user): void
    {
        $scheduledAt = now()->addMinutes(self::INITIAL_CONTACT_DELAY_MINUTES);

        $event = $this->recordLeadScheduledEvent->schedule(
            $lead,
            LeadScheduledEventType::FollowUp,
            $scheduledAt,
        );

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::FollowUpScheduled,
            __(':label scheduled for :date', [
                'label' => $event->ordinalLabel(),
                'date' => $scheduledAt->format('M j, Y g:i A'),
            ]),
            $user,
            metadata: [
                'scheduled_event_id' => $event->id,
                'sequence_number' => $event->sequence_number,
            ],
        );
    }
}

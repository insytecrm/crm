<?php

namespace App\Support;

use App\Actions\LogLeadActivity;
use App\Actions\RecalculateLeadScore;
use App\Enums\LeadActivityType;
use App\Enums\LeadStatus;
use App\Models\Lead;

class LeadAutoStatus
{
    /**
     * @var list<LeadStatus>
     */
    private const PipelineOrder = [
        LeadStatus::New,
        LeadStatus::Contacted,
        LeadStatus::Qualified,
        LeadStatus::FollowUp,
        LeadStatus::SiteVisit,
        LeadStatus::Negotiation,
        LeadStatus::Converted,
    ];

    public function __construct(
        private LogLeadActivity $logLeadActivity,
        private RecalculateLeadScore $recalculateLeadScore,
    ) {}

    public function applyForActivity(Lead $lead, LeadActivityType $activityType): void
    {
        $targetValue = config('crm_workflow.auto_status.'.$activityType->value);

        if (! is_string($targetValue) || $targetValue === '') {
            return;
        }

        $target = LeadStatus::tryFrom($targetValue);

        if ($target === null || ! $this->shouldAdvance($lead->status, $target)) {
            return;
        }

        $previous = $lead->status;
        $lead->update(['status' => $target]);

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::StatusChanged,
            __('Status changed from :from to :to', [
                'from' => $previous->label(),
                'to' => $target->label(),
            ]),
            metadata: [
                'from' => $previous->value,
                'to' => $target->value,
                'automatic' => true,
            ],
        );

        $this->recalculateLeadScore->handle($lead);
    }

    private function shouldAdvance(LeadStatus $current, LeadStatus $target): bool
    {
        if ($current->isClosed()) {
            return false;
        }

        $currentIndex = array_search($current, self::PipelineOrder, true);
        $targetIndex = array_search($target, self::PipelineOrder, true);

        if ($currentIndex === false || $targetIndex === false) {
            return false;
        }

        return $targetIndex > $currentIndex;
    }
}

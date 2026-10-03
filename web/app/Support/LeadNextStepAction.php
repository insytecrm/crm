<?php

namespace App\Support;

use App\Models\Lead;

class LeadNextStepAction
{
    public function __construct(
        private LeadHistorySummary $leadHistorySummary,
        private LeadNextStepHint $leadNextStepHint,
    ) {}

    /**
     * @return array{
     *     key: string,
     *     label: string,
     *     type: string,
     *     intent: ?string,
     *     modal: ?string,
     * }|null
     */
    public function for(Lead $lead): ?array
    {
        if ($lead->status->isClosed()) {
            return null;
        }

        $currentKey = $this->currentJourneyKey($lead);

        if ($currentKey === null) {
            return null;
        }

        $label = $this->leadNextStepHint->for($lead) ?? __('Take next step');

        return match ($currentKey) {
            'created', 'contacted' => [
                'key' => $currentKey,
                'label' => $label,
                'type' => 'log_interaction',
                'intent' => 'log-interaction',
                'modal' => 'log-interaction-'.$lead->id,
            ],
            'qualified' => [
                'key' => $currentKey,
                'label' => $label,
                'type' => 'log_interaction',
                'intent' => 'log-interaction',
                'modal' => 'log-interaction-'.$lead->id,
            ],
            'follow_up' => [
                'key' => $currentKey,
                'label' => $label,
                'type' => 'modal',
                'intent' => 'schedule-follow-up',
                'modal' => 'follow-up-'.$lead->id,
            ],
            'site_visit' => [
                'key' => $currentKey,
                'label' => $label,
                'type' => 'modal',
                'intent' => 'schedule-site-visit',
                'modal' => 'site-visit-'.$lead->id,
            ],
            'negotiation' => [
                'key' => $currentKey,
                'label' => $label,
                'type' => 'log_interaction',
                'intent' => 'log-interaction',
                'modal' => 'log-interaction-'.$lead->id,
            ],
            'booking' => [
                'key' => $currentKey,
                'label' => $label,
                'type' => 'modal',
                'intent' => 'create-booking',
                'modal' => $lead->hasBooking() ? null : 'create-booking',
            ],
            'agreement', 'invoice', 'payout' => [
                'key' => $currentKey,
                'label' => $label,
                'type' => 'deal',
                'intent' => 'deal-progress',
                'modal' => null,
            ],
            default => null,
        };
    }

    private function currentJourneyKey(Lead $lead): ?string
    {
        foreach ($this->leadHistorySummary->for($lead)['journey'] as $step) {
            if ($step['state'] === 'current') {
                return $step['key'];
            }
        }

        return null;
    }
}

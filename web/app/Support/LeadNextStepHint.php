<?php

namespace App\Support;

use App\Models\Lead;

class LeadNextStepHint
{
    public function __construct(private LeadHistorySummary $leadHistorySummary) {}

    public function for(Lead $lead): ?string
    {
        if ($lead->status->isClosed()) {
            return null;
        }

        $journey = $this->leadHistorySummary->for($lead)['journey'];

        foreach ($journey as $step) {
            if ($step['state'] !== 'current') {
                continue;
            }

            return $this->labelForKey($step['key']);
        }

        return null;
    }

    private function labelForKey(string $key): ?string
    {
        return match ($key) {
            'created', 'contacted' => __('Contact lead'),
            'qualified' => __('Qualify lead'),
            'follow_up' => __('Schedule follow-up'),
            'site_visit' => __('Schedule site visit'),
            'negotiation' => __('Move to negotiation'),
            'booking' => __('Create booking'),
            'agreement' => __('Mark agreement'),
            'invoice' => __('Create invoice'),
            'payout' => __('Mark payout paid'),
            default => null,
        };
    }
}

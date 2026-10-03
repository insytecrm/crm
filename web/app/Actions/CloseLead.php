<?php

namespace App\Actions;

use App\Enums\LeadActivityType;
use App\Enums\LeadClosingReason;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;

class CloseLead
{
    public function __construct(private LogLeadActivity $logLeadActivity) {}

    public function handle(Lead $lead, LeadClosingReason $reason, ?User $user = null): Lead
    {
        $user ??= auth()->user();

        $status = $reason === LeadClosingReason::Converted
            ? LeadStatus::Converted
            : LeadStatus::Lost;

        $lead->update([
            'status' => $status,
            'closing_reason' => $reason,
            'closed_at' => now(),
        ]);

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::StatusChanged,
            __('Lead closed: :reason', ['reason' => $reason->label()]),
            $user,
            [
                'closing_reason' => $reason->value,
                'status' => $status->value,
            ],
        );

        return $lead->fresh();
    }
}

<?php

namespace App\Actions;

use App\Enums\LeadActivityType;
use App\Enums\LeadClosingReason;
use App\Enums\LeadLostReason;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;

class MarkLeadLost
{
    public function __construct(private LogLeadActivity $logLeadActivity) {}

    /**
     * @param  list<LeadLostReason>  $lostReasons
     */
    public function handle(Lead $lead, array $lostReasons, string $closingNotes, ?User $user = null): Lead
    {
        $user ??= auth()->user();

        $lead->update([
            'status' => LeadStatus::Lost,
            'closing_reason' => LeadClosingReason::Lost,
            'lost_reasons' => array_map(fn (LeadLostReason $reason): string => $reason->value, $lostReasons),
            'closing_notes' => $closingNotes,
            'closed_at' => now(),
        ]);

        $reasonLabels = collect($lostReasons)
            ->map(fn (LeadLostReason $reason): string => $reason->label())
            ->implode(', ');

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::StatusChanged,
            __('Lead marked lost: :reasons', ['reasons' => $reasonLabels]),
            $user,
            [
                'lost_reasons' => array_map(fn (LeadLostReason $reason): string => $reason->value, $lostReasons),
                'closing_notes' => $closingNotes,
                'closing_reason' => LeadClosingReason::Lost->value,
                'status' => LeadStatus::Lost->value,
            ],
        );

        return $lead->fresh();
    }
}

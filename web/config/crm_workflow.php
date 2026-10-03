<?php

use App\Enums\LeadActivityType;
use App\Enums\LeadStatus;

return [

    /*
    |--------------------------------------------------------------------------
    | Stale lead threshold (days without activity)
    |--------------------------------------------------------------------------
    */
    'stale_lead_days' => (int) env('CRM_STALE_LEAD_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Auto status transitions after logging an interaction
    | Only applies when the new status is ahead of the current status.
    |--------------------------------------------------------------------------
    */
    'auto_status' => [
        LeadActivityType::CallMade->value => LeadStatus::Contacted->value,
        LeadActivityType::WhatsAppMessage->value => LeadStatus::Contacted->value,
        LeadActivityType::FollowUpScheduled->value => LeadStatus::FollowUp->value,
        LeadActivityType::SiteVisitScheduled->value => LeadStatus::SiteVisit->value,
    ],

];

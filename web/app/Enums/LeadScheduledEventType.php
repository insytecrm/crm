<?php

namespace App\Enums;

enum LeadScheduledEventType: string
{
    case FollowUp = 'follow_up';
    case SiteVisit = 'site_visit';

    public function label(): string
    {
        return match ($this) {
            self::FollowUp => __('Follow-up'),
            self::SiteVisit => __('Site Visit'),
        };
    }

    public function scheduledActivityType(): LeadActivityType
    {
        return match ($this) {
            self::FollowUp => LeadActivityType::FollowUpScheduled,
            self::SiteVisit => LeadActivityType::SiteVisitScheduled,
        };
    }

    public function completedActivityType(): LeadActivityType
    {
        return match ($this) {
            self::FollowUp => LeadActivityType::FollowUpCompleted,
            self::SiteVisit => LeadActivityType::SiteVisitCompleted,
        };
    }
}

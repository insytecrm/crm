<?php

namespace App\Enums;

enum AutomationConditionField: string
{
    case Status = 'lead.status';
    case Source = 'lead.source';
    case Budget = 'lead.budget';
    case PropertyType = 'lead.property_type';
    case Location = 'lead.location';
    case SiteVisitOutcome = 'site_visit.outcome';
    case PropertyProject = 'lead.property_project';

    public function label(): string
    {
        return match ($this) {
            self::Status => __('Lead status'),
            self::Source => __('Lead source'),
            self::Budget => __('Budget'),
            self::PropertyType => __('Property type'),
            self::Location => __('Location'),
            self::SiteVisitOutcome => __('Site visit outcome'),
            self::PropertyProject => __('Property / project'),
        };
    }
}

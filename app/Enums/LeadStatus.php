<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case FollowUp = 'follow_up';
    case SiteVisit = 'site_visit';
    case Negotiation = 'negotiation';
    case Converted = 'converted';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::New => __('New'),
            self::Contacted => __('Contacted'),
            self::Qualified => __('Qualified'),
            self::FollowUp => __('Follow-up'),
            self::SiteVisit => __('Site Visit'),
            self::Negotiation => __('Negotiation'),
            self::Converted => __('Converted'),
            self::Lost => __('Lost'),
        };
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::Converted, self::Lost], true);
    }

    /**
     * @return list<self>
     */
    public static function manuallySelectableCases(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $status): bool => ! in_array($status, [self::Converted, self::Lost], true),
        ));
    }
}

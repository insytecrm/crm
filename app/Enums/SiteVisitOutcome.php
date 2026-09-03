<?php

namespace App\Enums;

enum SiteVisitOutcome: string
{
    case Interested = 'interested';
    case NotInterested = 'not_interested';
    case PriceConcern = 'price_concern';
    case LocationConcern = 'location_concern';
    case NoDecision = 'no_decision';
    case ReadyToBook = 'ready_to_book';
    case Other = 'other';

    /**
     * @return list<self>
     */
    public static function options(): array
    {
        return self::cases();
    }

    public function label(): string
    {
        return match ($this) {
            self::Interested => __('Interested'),
            self::NotInterested => __('Not interested'),
            self::PriceConcern => __('Price concern'),
            self::LocationConcern => __('Location concern'),
            self::NoDecision => __('No decision'),
            self::ReadyToBook => __('Ready to book'),
            self::Other => __('Other'),
        };
    }
}

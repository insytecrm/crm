<?php

namespace App\Enums;

enum LeadScheduledEventStatus: string
{
    case Scheduled = 'scheduled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => __('Scheduled'),
            self::Completed => __('Completed'),
        };
    }
}

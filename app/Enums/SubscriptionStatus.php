<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Trial = 'trial';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';
    case Paused = 'paused';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Trial => __('Trial'),
            self::PastDue => __('Past Due'),
            self::Cancelled => __('Cancelled'),
            self::Paused => __('Paused'),
            self::Suspended => __('Suspended'),
        };
    }
}

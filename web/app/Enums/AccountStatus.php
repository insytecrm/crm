<?php

namespace App\Enums;

enum AccountStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case Inactive = 'inactive';
    case Cancelled = 'cancelled';
    case TrialEnded = 'trial_ended';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Trial => __('Trial'),
            self::Active => __('Active'),
            self::Inactive => __('Inactive'),
            self::Cancelled => __('Cancelled'),
            self::TrialEnded => __('Trial Ended'),
            self::Suspended => __('Suspended'),
        };
    }
}

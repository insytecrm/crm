<?php

namespace App\Enums;

enum BillingPaymentType: string
{
    case Subscription = 'subscription';
    case Renewal = 'renewal';
    case Upgrade = 'upgrade';
    case AddOn = 'add_on';

    public function label(): string
    {
        return match ($this) {
            self::Subscription => __('Subscription'),
            self::Renewal => __('Renewal'),
            self::Upgrade => __('Upgrade'),
            self::AddOn => __('Add-on'),
        };
    }
}

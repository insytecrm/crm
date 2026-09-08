<?php

namespace App\Enums;

enum BillingCycle: string
{
    case Monthly = 'monthly';
    case Annual = 'annual';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => __('Monthly'),
            self::Annual => __('Annual'),
        };
    }

    public function priceSuffix(): string
    {
        return match ($this) {
            self::Monthly => __(' / month'),
            self::Annual => __(' / year'),
        };
    }
}

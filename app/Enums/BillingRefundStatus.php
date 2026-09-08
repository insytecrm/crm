<?php

namespace App\Enums;

enum BillingRefundStatus: string
{
    case Requested = 'requested';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Requested => __('Requested'),
            self::Processing => __('Processing'),
            self::Completed => __('Completed'),
            self::Failed => __('Failed'),
        };
    }
}

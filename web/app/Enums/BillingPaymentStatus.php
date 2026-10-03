<?php

namespace App\Enums;

enum BillingPaymentStatus: string
{
    case Paid = 'paid';
    case Pending = 'pending';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Paid => __('Paid'),
            self::Pending => __('Pending'),
            self::Failed => __('Failed'),
            self::Refunded => __('Refunded'),
        };
    }
}

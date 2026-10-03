<?php

namespace App\Enums;

enum BillingInvoiceStatus: string
{
    case Paid = 'paid';
    case Pending = 'pending';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Paid => __('Paid'),
            self::Pending => __('Pending'),
            self::Overdue => __('Overdue'),
            self::Cancelled => __('Cancelled'),
        };
    }
}

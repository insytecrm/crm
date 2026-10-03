<?php

namespace App\Enums;

enum InvoicePaymentFilter: string
{
    case All = 'all';
    case Pending = 'pending';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::All => __('All'),
            self::Pending => __('Pending'),
            self::Paid => __('Paid'),
        };
    }

    public function isActive(): bool
    {
        return $this !== self::All;
    }

    public static function fromRequest(?string $value): self
    {
        return self::tryFrom($value) ?? self::All;
    }
}

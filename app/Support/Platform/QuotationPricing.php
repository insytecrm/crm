<?php

namespace App\Support\Platform;

final class QuotationPricing
{
    public const DefaultTaxRate = 0.18;

    /**
     * @return array{
     *     plan_price: int,
     *     discount_amount: int,
     *     taxable: int,
     *     tax_amount: int,
     *     total: int
     * }
     */
    public static function calculate(int $planPrice, int $discountAmount, ?int $taxAmount = null): array
    {
        $planPrice = max(0, $planPrice);
        $discountAmount = max(0, min($discountAmount, $planPrice));
        $taxable = max($planPrice - $discountAmount, 0);
        $resolvedTax = $taxAmount ?? (int) round($taxable * self::DefaultTaxRate);

        return [
            'plan_price' => $planPrice,
            'discount_amount' => $discountAmount,
            'taxable' => $taxable,
            'tax_amount' => max(0, $resolvedTax),
            'total' => $taxable + max(0, $resolvedTax),
        ];
    }

    public static function defaultTax(int $planPrice, int $discountAmount): int
    {
        return self::calculate($planPrice, $discountAmount)['tax_amount'];
    }
}

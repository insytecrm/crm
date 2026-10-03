<?php

namespace App\Support\Platform;

final class BillingMoney
{
    public static function format(int $amount): string
    {
        return '₹'.number_format($amount);
    }
}

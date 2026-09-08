<?php

namespace App\Support\Microsite;

class MicrositeMoney
{
    public static function rupees(?int $amount): ?string
    {
        if ($amount === null) {
            return null;
        }

        if ($amount >= 10_000_000) {
            return '₹'.self::trimmed($amount / 10_000_000).' Cr';
        }

        if ($amount >= 100_000) {
            return '₹'.self::trimmed($amount / 100_000).' L';
        }

        return '₹'.number_format($amount);
    }

    public static function range(?int $from, ?int $to): ?string
    {
        $start = self::rupees($from);
        $end = self::rupees($to);

        if ($start !== null && $end !== null) {
            return $from === $to ? $start : $start.' – '.$end;
        }

        return $start ?? $end;
    }

    private static function trimmed(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}

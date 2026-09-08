<?php

namespace App\Support\Platform;

use Illuminate\Support\Carbon;

final class BillingPeriod
{
    /**
     * @return array{start: Carbon, end: Carbon, previous_start: Carbon, previous_end: Carbon, key: string, label: string}
     */
    public static function fromRequest(?string $range, ?string $from = null, ?string $to = null): array
    {
        $now = now()->timezone(config('app.timezone'));
        $key = $range ?: 'this_month';

        [$start, $end, $label] = match ($key) {
            'last_month' => [
                $now->copy()->subMonthNoOverflow()->startOfMonth(),
                $now->copy()->subMonthNoOverflow()->endOfMonth(),
                __('Last Month'),
            ],
            'this_quarter' => [
                $now->copy()->firstOfQuarter()->startOfDay(),
                $now->copy()->lastOfQuarter()->endOfDay(),
                __('This Quarter'),
            ],
            'this_year' => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
                __('This Year'),
            ],
            'custom' => [
                filled($from) ? Carbon::parse($from)->startOfDay() : $now->copy()->startOfMonth(),
                filled($to) ? Carbon::parse($to)->endOfDay() : $now->copy()->endOfDay(),
                __('Custom'),
            ],
            default => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
                __('This Month'),
            ],
        };

        $days = max($start->diffInDays($end) + 1, 1);
        $previousEnd = $start->copy()->subDay()->endOfDay();
        $previousStart = $previousEnd->copy()->subDays($days - 1)->startOfDay();

        return [
            'start' => $start,
            'end' => $end,
            'previous_start' => $previousStart,
            'previous_end' => $previousEnd,
            'key' => $key === 'custom' || in_array($key, ['this_month', 'last_month', 'this_quarter', 'this_year'], true)
                ? $key
                : 'this_month',
            'label' => $label,
        ];
    }

    /**
     * @return array{start: Carbon, end: Carbon, key: string}
     */
    public static function chartWindow(?string $window): array
    {
        $now = now()->timezone(config('app.timezone'));
        $key = $window ?: '30_days';

        return match ($key) {
            '7_days' => [
                'start' => $now->copy()->subDays(6)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'key' => '7_days',
            ],
            '3_months' => [
                'start' => $now->copy()->subMonthsNoOverflow(2)->startOfMonth(),
                'end' => $now->copy()->endOfDay(),
                'key' => '3_months',
            ],
            '12_months' => [
                'start' => $now->copy()->subMonthsNoOverflow(11)->startOfMonth(),
                'end' => $now->copy()->endOfDay(),
                'key' => '12_months',
            ],
            default => [
                'start' => $now->copy()->subDays(29)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'key' => '30_days',
            ],
        };
    }
}

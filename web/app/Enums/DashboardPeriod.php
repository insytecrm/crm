<?php

namespace App\Enums;

enum DashboardPeriod: string
{
    case Today = 'today';
    case ThisWeek = 'this_week';
    case ThisMonth = 'this_month';
    case ThisQuarter = 'this_quarter';
    case ThisYear = 'this_year';
    case AllTime = 'all_time';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Today => __('Today'),
            self::ThisWeek => __('This Week'),
            self::ThisMonth => __('This Month'),
            self::ThisQuarter => __('This Quarter'),
            self::ThisYear => __('This Year'),
            self::AllTime => __('All Time'),
            self::Custom => __('Custom Range'),
        };
    }

    public static function fromRequest(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::ThisMonth;
    }
}

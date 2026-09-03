<?php

namespace App\Enums;

enum ActivityFilter: string
{
    case Today = 'today';
    case Upcoming = 'upcoming';
    case Overdue = 'overdue';
    case Completed = 'completed';
    case All = 'all';

    public function label(): string
    {
        return match ($this) {
            self::Today => __('Today'),
            self::Upcoming => __('Upcoming'),
            self::Overdue => __('Overdue'),
            self::Completed => __('Completed'),
            self::All => __('All'),
        };
    }

    public function listHeading(): string
    {
        return match ($this) {
            self::Today => __('Today\'s Activities'),
            self::Upcoming => __('Upcoming Activities'),
            self::Overdue => __('Overdue Activities'),
            self::Completed => __('Completed Activities'),
            self::All => __('All Activities'),
        };
    }

    public function emptyMessage(): string
    {
        return match ($this) {
            self::Completed => __('No completed follow-ups or site visits yet.'),
            self::All => __('No activities found.'),
            default => __('No activities found for this filter.'),
        };
    }

    public static function fromRequest(?string $value): self
    {
        return self::tryFrom($value) ?? self::All;
    }
}

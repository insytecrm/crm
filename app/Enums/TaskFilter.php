<?php

namespace App\Enums;

enum TaskFilter: string
{
    case Today = 'today';
    case Upcoming = 'upcoming';
    case Completed = 'completed';
    case All = 'all';

    public function label(): string
    {
        return match ($this) {
            self::Today => __('Today'),
            self::Upcoming => __('Upcoming'),
            self::Completed => __('Completed'),
            self::All => __('All'),
        };
    }

    public function listHeading(): string
    {
        return match ($this) {
            self::Today => __('Today\'s Tasks'),
            self::Upcoming => __('Upcoming Tasks'),
            self::Completed => __('Completed Tasks'),
            self::All => __('All Tasks'),
        };
    }

    public function emptyMessage(): string
    {
        return match ($this) {
            self::Completed => __('No completed tasks yet.'),
            self::All => __('No tasks found.'),
            default => __('No tasks found for this filter.'),
        };
    }

    public static function fromRequest(?string $value): self
    {
        return self::tryFrom($value) ?? self::Today;
    }
}

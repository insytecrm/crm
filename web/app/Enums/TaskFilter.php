<?php

namespace App\Enums;

enum TaskFilter: string
{
    case All = 'all';
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::All => __('Total Tasks'),
            self::Pending => __('Pending'),
            self::InProgress => __('In Progress'),
            self::Completed => __('Completed'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function listHeading(): string
    {
        return match ($this) {
            self::All => __('All Tasks'),
            self::Pending => __('Pending Tasks'),
            self::InProgress => __('In Progress Tasks'),
            self::Completed => __('Completed Tasks'),
            self::Cancelled => __('Cancelled Tasks'),
        };
    }

    public function emptyMessage(): string
    {
        return match ($this) {
            self::Pending => __('No pending tasks.'),
            self::InProgress => __('No in-progress tasks.'),
            self::Completed => __('No completed tasks yet.'),
            self::Cancelled => __('No cancelled tasks.'),
            self::All => __('No tasks found.'),
        };
    }

    public static function fromRequest(?string $value): self
    {
        return self::tryFrom($value) ?? self::All;
    }
}

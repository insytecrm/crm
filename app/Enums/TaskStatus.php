<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Complete = 'complete';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::InProgress => __('In Progress'),
            self::Complete => __('Complete'),
            self::Cancelled => __('Cancelled'),
        };
    }

    /**
     * @return list<self>
     */
    public static function timeline(): array
    {
        return [
            self::Pending,
            self::InProgress,
            self::Complete,
            self::Cancelled,
        ];
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Complete, self::Cancelled], true);
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::InProgress, self::Complete, self::Cancelled],
            self::InProgress => [self::Complete, self::Cancelled],
            self::Complete, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }
}

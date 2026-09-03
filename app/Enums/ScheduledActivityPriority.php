<?php

namespace App\Enums;

enum ScheduledActivityPriority: string
{
    case High = 'high';
    case Normal = 'normal';
    case Low = 'low';

    /**
     * @return list<self>
     */
    public static function options(): array
    {
        return [
            self::High,
            self::Normal,
            self::Low,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::High => __('High'),
            self::Normal => __('Normal'),
            self::Low => __('Low'),
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::High => 0,
            self::Normal => 1,
            self::Low => 2,
        };
    }
}

<?php

namespace App\Enums;

enum ScheduledActivityStage: string
{
    case Pending = 'pending';
    case Overdue = 'overdue';
    case Completed = 'completed';
    case Rescheduled = 'rescheduled';

    /**
     * @return list<self>
     */
    public static function tabs(): array
    {
        return [
            self::Pending,
            self::Overdue,
            self::Completed,
            self::Rescheduled,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Upcoming'),
            self::Overdue => __('Overdue'),
            self::Completed => __('Complete'),
            self::Rescheduled => __('Reschedule'),
        };
    }

    public function listHeading(string $activityLabel): string
    {
        return match ($this) {
            self::Pending => __('Upcoming :activities', ['activities' => $activityLabel]),
            self::Overdue => __('Overdue :activities', ['activities' => $activityLabel]),
            self::Completed => __('Complete :activities', ['activities' => $activityLabel]),
            self::Rescheduled => __('Rescheduled :activities', ['activities' => $activityLabel]),
        };
    }

    public function emptyMessage(string $activityLabel): string
    {
        return match ($this) {
            self::Pending => __('No upcoming :activities.', ['activities' => strtolower($activityLabel)]),
            self::Overdue => __('No overdue :activities.', ['activities' => strtolower($activityLabel)]),
            self::Completed => __('No complete :activities yet.', ['activities' => strtolower($activityLabel)]),
            self::Rescheduled => __('No rescheduled :activities.', ['activities' => strtolower($activityLabel)]),
        };
    }

    public function accent(): string
    {
        return match ($this) {
            self::Pending => 'sky',
            self::Overdue => 'amber',
            self::Completed => 'emerald',
            self::Rescheduled => 'cyan',
        };
    }

    public static function fromRequest(?string $value): self
    {
        if ($value === 'upcoming') {
            return self::Pending;
        }

        if ($value === 'reschedule') {
            return self::Rescheduled;
        }

        return self::tryFrom($value) ?? self::Pending;
    }
}

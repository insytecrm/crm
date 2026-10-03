<?php

namespace App\Enums;

enum ScheduledActivityNextStep: string
{
    case ScheduleFollowUp = 'schedule_follow_up';
    case ScheduleSiteVisit = 'schedule_site_visit';
    case CreateTask = 'create_task';
    case None = 'none';

    /**
     * @return list<self>
     */
    public static function options(): array
    {
        return [
            self::ScheduleFollowUp,
            self::ScheduleSiteVisit,
            self::CreateTask,
            self::None,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::ScheduleFollowUp => __('Schedule follow-up'),
            self::ScheduleSiteVisit => __('Schedule site visit'),
            self::CreateTask => __('Create task'),
            self::None => __('Nothing for now'),
        };
    }
}

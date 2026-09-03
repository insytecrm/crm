<?php

namespace App\Enums;

enum SiteVisitNextStep: string
{
    case ScheduleFollowUp = 'schedule_follow_up';
    case ScheduleSiteVisit = 'schedule_site_visit';
    case CreateTask = 'create_task';
    case CreateBooking = 'create_booking';
    case None = 'none';

    /**
     * @return list<self>
     */
    public static function options(): array
    {
        return self::cases();
    }

    public function label(): string
    {
        return match ($this) {
            self::ScheduleFollowUp => __('Follow-up'),
            self::ScheduleSiteVisit => __('Site visit'),
            self::CreateTask => __('Task'),
            self::CreateBooking => __('Create booking'),
            self::None => __('No action'),
        };
    }
}

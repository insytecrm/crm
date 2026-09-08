<?php

namespace App\Enums;

enum AutomationTrigger: string
{
    case LeadCreated = 'lead_created';
    case StatusChanged = 'status_changed';
    case LeadUpdated = 'lead_updated';
    case LeadMarkedPriority = 'lead_marked_priority';
    case LeadMarkedLost = 'lead_marked_lost';
    case LeadConverted = 'lead_converted';

    case FollowUpScheduled = 'follow_up_scheduled';
    case FollowUpCompleted = 'follow_up_completed';
    case FollowUpRescheduled = 'follow_up_rescheduled';
    case FollowUpOverdue = 'follow_up_overdue';
    case FollowUpCancelled = 'follow_up_cancelled';

    case SiteVisitScheduled = 'site_visit_scheduled';
    case SiteVisitCompleted = 'site_visit_completed';
    case SiteVisitRescheduled = 'site_visit_rescheduled';
    case SiteVisitCancelled = 'site_visit_cancelled';
    case SiteVisitNoShow = 'site_visit_no_show';
    case SiteVisitOutcomeUpdated = 'site_visit_outcome_updated';

    case TaskCreated = 'task_created';
    case TaskCompleted = 'task_completed';
    case TaskOverdue = 'task_overdue';

    case BookingCreated = 'booking_created';
    case BookingUpdated = 'booking_updated';
    case BookingCancelled = 'booking_cancelled';
    case AgreementMarked = 'agreement_marked';

    case NoActivityForPeriod = 'no_activity_for_period';
    case LeadInStatusForDays = 'lead_in_status_for_days';
    case FollowUpOverdueByHours = 'follow_up_overdue_by_hours';
    case SiteVisitCompletedDaysAgo = 'site_visit_completed_days_ago';
    case BookingCreatedDaysAgo = 'booking_created_days_ago';

    public function label(): string
    {
        return match ($this) {
            self::LeadCreated => __('Lead created'),
            self::StatusChanged => __('Lead status changed'),
            self::LeadUpdated => __('Lead updated'),
            self::LeadMarkedPriority => __('Lead marked priority'),
            self::LeadMarkedLost => __('Lead marked lost'),
            self::LeadConverted => __('Lead converted'),
            self::FollowUpScheduled => __('Follow-up scheduled'),
            self::FollowUpCompleted => __('Follow-up completed'),
            self::FollowUpRescheduled => __('Follow-up rescheduled'),
            self::FollowUpOverdue => __('Follow-up overdue'),
            self::FollowUpCancelled => __('Follow-up cancelled'),
            self::SiteVisitScheduled => __('Site visit scheduled'),
            self::SiteVisitCompleted => __('Site visit completed'),
            self::SiteVisitRescheduled => __('Site visit rescheduled'),
            self::SiteVisitCancelled => __('Site visit cancelled'),
            self::SiteVisitNoShow => __('Site visit no-show'),
            self::SiteVisitOutcomeUpdated => __('Site visit outcome updated'),
            self::TaskCreated => __('Task created'),
            self::TaskCompleted => __('Task completed'),
            self::TaskOverdue => __('Task overdue'),
            self::BookingCreated => __('Booking created'),
            self::BookingUpdated => __('Booking updated'),
            self::BookingCancelled => __('Booking cancelled'),
            self::AgreementMarked => __('Agreement marked'),
            self::NoActivityForPeriod => __('No activity for X hours/days'),
            self::LeadInStatusForDays => __('Lead in status for X days'),
            self::FollowUpOverdueByHours => __('Follow-up overdue by X hours'),
            self::SiteVisitCompletedDaysAgo => __('Site visit completed X days ago'),
            self::BookingCreatedDaysAgo => __('Booking created X days ago'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::LeadCreated => __('Start when a new lead enters CRM.'),
            self::StatusChanged => __('Start when a lead moves to another stage.'),
            self::LeadUpdated => __('Start when lead information is changed.'),
            self::LeadMarkedPriority => __('Start when a lead becomes a priority lead.'),
            self::LeadMarkedLost => __('Start when a lead is marked lost.'),
            self::LeadConverted => __('Start when a lead becomes a customer/booking.'),
            self::FollowUpScheduled => __('Start when a follow-up is scheduled.'),
            self::FollowUpCompleted => __('Start after a salesperson completes a follow-up.'),
            self::FollowUpRescheduled => __('Start when follow-up date/time changes.'),
            self::FollowUpOverdue => __('Start when a salesperson misses a follow-up.'),
            self::FollowUpCancelled => __('Start when a follow-up is cancelled.'),
            self::SiteVisitScheduled => __('Start when a site visit is booked.'),
            self::SiteVisitCompleted => __('Start after a customer completes a site visit.'),
            self::SiteVisitRescheduled => __('Start when visit date/time changes.'),
            self::SiteVisitCancelled => __('Start when a visit is cancelled.'),
            self::SiteVisitNoShow => __('Start when a customer does not attend.'),
            self::SiteVisitOutcomeUpdated => __('Start based on visit result/outcome.'),
            self::TaskCreated => __('Start when a new task is created.'),
            self::TaskCompleted => __('Start after a salesperson completes a task.'),
            self::TaskOverdue => __('Start when a task is missed.'),
            self::BookingCreated => __('Start after a lead is booked.'),
            self::BookingUpdated => __('Start when booking details change.'),
            self::BookingCancelled => __('Start when a booking is cancelled.'),
            self::AgreementMarked => __('Start when agreement is completed/marked.'),
            self::NoActivityForPeriod => __('Alert about inactive leads after a quiet period.'),
            self::LeadInStatusForDays => __('Push stagnant leads stuck in one stage.'),
            self::FollowUpOverdueByHours => __('Escalate a missed follow-up after extra hours.'),
            self::SiteVisitCompletedDaysAgo => __('Ensure post-visit follow-up happens.'),
            self::BookingCreatedDaysAgo => __('Follow up on agreement/payment process.'),
        };
    }

    public function isReady(): bool
    {
        return true;
    }

    public function pickerSection(): string
    {
        return match ($this) {
            self::LeadCreated, self::StatusChanged, self::LeadUpdated, self::LeadMarkedPriority, self::LeadMarkedLost, self::LeadConverted => 'lead',
            self::FollowUpScheduled, self::FollowUpCompleted, self::FollowUpRescheduled, self::FollowUpOverdue, self::FollowUpCancelled => 'follow_up',
            self::SiteVisitScheduled, self::SiteVisitCompleted, self::SiteVisitRescheduled, self::SiteVisitCancelled, self::SiteVisitNoShow, self::SiteVisitOutcomeUpdated => 'site_visit',
            self::TaskCreated, self::TaskCompleted, self::TaskOverdue => 'tasks',
            self::BookingCreated, self::BookingUpdated, self::BookingCancelled, self::AgreementMarked => 'booking',
            self::NoActivityForPeriod, self::LeadInStatusForDays, self::FollowUpOverdueByHours, self::SiteVisitCompletedDaysAgo, self::BookingCreatedDaysAgo => 'time_based',
        };
    }

    public function pickerSectionLabel(): string
    {
        return match ($this->pickerSection()) {
            'lead' => __('Lead'),
            'follow_up' => __('Follow-up'),
            'site_visit' => __('Site visit'),
            'tasks' => __('Tasks'),
            'booking' => __('Booking'),
            'time_based' => __('Time-based'),
        };
    }

    /**
     * @return list<self>
     */
    public static function pickerCases(): array
    {
        return self::cases();
    }

    public static function fromActivity(LeadActivityType $type): ?self
    {
        $trigger = self::tryFrom($type->value);

        if ($trigger === null || ! $trigger->isReady()) {
            return null;
        }

        return $trigger;
    }
}

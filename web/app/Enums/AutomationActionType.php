<?php

namespace App\Enums;

enum AutomationActionType: string
{
    case CreateTask = 'create_task';
    case AddNote = 'add_note';
    case ChangeStatus = 'change_status';
    case ScheduleFollowUp = 'schedule_follow_up';
    case CreateSiteVisit = 'create_site_visit';
    case RescheduleFollowUp = 'reschedule_follow_up';
    case MarkPriority = 'mark_priority';
    case RemovePriority = 'remove_priority';
    case NotifySalesperson = 'notify_salesperson';
    case NotifyTeamLeader = 'notify_team_leader';
    case NotifyManager = 'notify_manager';
    case Wait = 'wait';
    case SendWhatsApp = 'send_whatsapp';
    case SendEmail = 'send_email';
    case SendSms = 'send_sms';

    public function label(): string
    {
        return match ($this) {
            self::CreateTask => __('Create task'),
            self::AddNote => __('Add note'),
            self::ChangeStatus => __('Change lead status'),
            self::ScheduleFollowUp => __('Schedule follow-up'),
            self::CreateSiteVisit => __('Create site visit'),
            self::RescheduleFollowUp => __('Reschedule follow-up'),
            self::MarkPriority => __('Mark priority'),
            self::RemovePriority => __('Remove priority'),
            self::NotifySalesperson => __('Notify salesperson'),
            self::NotifyTeamLeader => __('Notify team leader'),
            self::NotifyManager => __('Notify manager'),
            self::Wait => __('Wait / delay'),
            self::SendWhatsApp => __('Send WhatsApp'),
            self::SendEmail => __('Send email'),
            self::SendSms => __('Send SMS'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CreateTask => __('Create work for the salesperson.'),
            self::AddNote => __('Add information to the lead timeline.'),
            self::ChangeStatus => __('Move the lead to another sales stage.'),
            self::ScheduleFollowUp => __('Create the next follow-up for the salesperson.'),
            self::CreateSiteVisit => __('Schedule a customer site visit.'),
            self::RescheduleFollowUp => __('Change the salesperson follow-up date/time.'),
            self::MarkPriority => __('Mark the lead as important/hot.'),
            self::RemovePriority => __('Remove priority from the lead.'),
            self::NotifySalesperson => __('Alert the assigned salesperson with a task.'),
            self::NotifyTeamLeader => __('Alert the team leader with a task.'),
            self::NotifyManager => __('Alert a sales manager with a task.'),
            self::Wait => __('Wait before performing the next action.'),
            self::SendWhatsApp => __('Send a WhatsApp message to the lead.'),
            self::SendEmail => __('Send an email to the lead.'),
            self::SendSms => __('Send an SMS to the lead.'),
        };
    }

    public function isOutput(): bool
    {
        return in_array($this, [self::SendWhatsApp, self::SendEmail, self::SendSms], true);
    }

    public function isSelectable(): bool
    {
        return ! $this->isOutput();
    }

    public function pickerSection(): string
    {
        return match ($this) {
            self::CreateTask, self::AddNote, self::ChangeStatus, self::MarkPriority, self::RemovePriority => 'crm',
            self::ScheduleFollowUp, self::CreateSiteVisit, self::RescheduleFollowUp => 'activities',
            self::NotifySalesperson, self::NotifyTeamLeader, self::NotifyManager => 'alerts',
            self::Wait => 'flow',
            self::SendWhatsApp, self::SendEmail, self::SendSms => 'messages',
        };
    }

    public function pickerSectionLabel(): string
    {
        return match ($this->pickerSection()) {
            'crm' => __('CRM'),
            'activities' => __('Activities'),
            'alerts' => __('Alerts'),
            'flow' => __('Flow'),
            'messages' => __('Messages'),
        };
    }

    /**
     * @return list<self>
     */
    public static function pickerCases(): array
    {
        return self::cases();
    }
}

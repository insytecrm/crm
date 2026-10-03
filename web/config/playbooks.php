<?php

use App\Enums\AutomationActionType;
use App\Enums\AutomationTrigger;

return [

    'new_lead_contact_task' => [
        'label' => 'Contact every new lead',
        'description' => 'Creates a task for the assignee when a lead is created.',
        'trigger' => AutomationTrigger::LeadCreated->value,
        'actions' => [
            [
                'type' => AutomationActionType::CreateTask->value,
                'title' => 'Contact new lead',
            ],
        ],
    ],

    'follow_up_overdue_notify' => [
        'label' => 'Notify when follow-up is overdue',
        'description' => 'Alerts the salesperson when a follow-up passes its due time.',
        'trigger' => AutomationTrigger::FollowUpOverdue->value,
        'actions' => [
            [
                'type' => AutomationActionType::NotifySalesperson->value,
                'title' => 'Follow-up overdue',
                'body' => 'A scheduled follow-up is overdue. Please complete or reschedule it.',
            ],
        ],
    ],

    'site_visit_completed_follow_up' => [
        'label' => 'Schedule follow-up after site visit',
        'description' => 'Creates a follow-up task after a site visit is completed.',
        'trigger' => AutomationTrigger::SiteVisitCompleted->value,
        'actions' => [
            [
                'type' => AutomationActionType::ScheduleFollowUp->value,
                'delay_hours' => 24,
            ],
        ],
    ],

];

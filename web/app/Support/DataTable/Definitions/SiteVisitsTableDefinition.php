<?php

namespace App\Support\DataTable\Definitions;

use App\Models\User;

class SiteVisitsTableDefinition extends ScheduledEventsTableDefinition
{
    public function key(): string
    {
        return 'site_visits';
    }

    /**
     * @return array<string, bool>
     */
    public function defaultColumns(): array
    {
        return [
            'lead' => true,
            'phone' => true,
            'assigned_to' => true,
            'activity' => true,
            'property' => true,
            'priority' => true,
            'scheduled' => true,
            'completion_method' => false,
            'completion_outcome' => false,
            'next_step' => false,
            'stage' => true,
            'actions' => true,
        ];
    }

    /**
     * @return list<string>
     */
    public function requiredColumns(): array
    {
        return ['lead'];
    }

    /**
     * @return array<string, string>
     */
    public function columnLabels(): array
    {
        return [
            'lead' => __('Lead'),
            'phone' => __('Phone'),
            'assigned_to' => __('Assigned To'),
            'activity' => __('Activity'),
            'property' => __('Property'),
            'priority' => __('Priority'),
            'scheduled' => __('Scheduled'),
            'completion_method' => __('Attended'),
            'completion_outcome' => __('Outcome'),
            'next_step' => __('Next action'),
            'stage' => __('Stage'),
            'actions' => __('Actions'),
        ];
    }

    public function authorizeBulkDelete(User $user): bool
    {
        return true;
    }
}

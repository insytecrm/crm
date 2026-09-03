<?php

namespace App\Support\DataTable\Definitions;

use App\Models\LeadScheduledEvent;
use App\Models\User;
use App\Support\DataTable\AbstractDataTableDefinition;

class ActivitiesSiteVisitsTableDefinition extends AbstractDataTableDefinition
{
    public function key(): string
    {
        return 'activities_site_visits';
    }

    /**
     * @return array<string, bool>
     */
    public function defaultColumns(): array
    {
        return [
            'time' => true,
            'activity' => true,
            'lead' => true,
            'property' => true,
            'notes' => true,
            'status' => true,
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
            'time' => __('Time'),
            'activity' => __('Activity'),
            'lead' => __('Lead'),
            'property' => __('Property'),
            'notes' => __('Notes'),
            'status' => __('Status'),
            'actions' => __('Actions'),
        ];
    }

    public function modelClass(): string
    {
        return LeadScheduledEvent::class;
    }

    public function bulkDeleteParameterName(): string
    {
        return 'event_ids';
    }

    public function authorizeBulkDelete(User $user): bool
    {
        return true;
    }
}

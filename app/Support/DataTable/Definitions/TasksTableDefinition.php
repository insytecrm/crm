<?php

namespace App\Support\DataTable\Definitions;

use App\Models\LeadTask;
use App\Models\User;
use App\Support\DataTable\AbstractDataTableDefinition;

class TasksTableDefinition extends AbstractDataTableDefinition
{
    public function key(): string
    {
        return 'tasks';
    }

    /**
     * @return array<string, bool>
     */
    public function defaultColumns(): array
    {
        return [
            'title' => true,
            'related_to' => true,
            'assigned_to' => true,
            'due_date' => true,
            'status' => true,
            'description' => false,
            'actions' => true,
        ];
    }

    /**
     * @return list<string>
     */
    public function requiredColumns(): array
    {
        return ['title'];
    }

    /**
     * @return array<string, string>
     */
    public function columnLabels(): array
    {
        return [
            'title' => __('Task'),
            'related_to' => __('Related To'),
            'assigned_to' => __('Assigned To'),
            'due_date' => __('Due Date'),
            'status' => __('Status'),
            'description' => __('Description'),
            'actions' => __('Actions'),
        ];
    }

    public function modelClass(): string
    {
        return LeadTask::class;
    }

    public function bulkDeleteParameterName(): string
    {
        return 'task_ids';
    }

    public function authorizeBulkDelete(User $user): bool
    {
        return true;
    }
}

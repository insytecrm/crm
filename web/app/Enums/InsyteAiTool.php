<?php

namespace App\Enums;

use stdClass;

enum InsyteAiTool: string
{
    case SearchLeads = 'search_leads';
    case GetLead = 'get_lead';
    case ListToday = 'list_today';
    case ScheduleFollowUp = 'schedule_follow_up';
    case ScheduleSiteVisit = 'schedule_site_visit';
    case CompleteFollowUp = 'complete_follow_up';
    case CompleteSiteVisit = 'complete_site_visit';
    case CreateTask = 'create_task';
    case CompleteTask = 'complete_task';
    case AddNote = 'add_note';

    public function permission(): TenantPermission
    {
        return match ($this) {
            self::SearchLeads, self::GetLead => TenantPermission::LeadsView,
            self::ListToday => TenantPermission::TasksView,
            self::ScheduleFollowUp, self::ScheduleSiteVisit, self::CompleteFollowUp, self::CompleteSiteVisit => TenantPermission::ActivitiesManage,
            self::CreateTask, self::CompleteTask => TenantPermission::TasksManage,
            self::AddNote => TenantPermission::LeadsUpdate,
        };
    }

    public function isMutation(): bool
    {
        return match ($this) {
            self::SearchLeads, self::GetLead, self::ListToday => false,
            default => true,
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function definitions(): array
    {
        return array_map(
            fn (self $tool): array => $tool->definition(),
            self::cases(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $properties = $this->properties();
        $required = $this->required();

        $parameters = [
            'type' => 'object',
            'properties' => $properties === [] ? new stdClass : $properties,
            'additionalProperties' => false,
        ];

        if ($required !== []) {
            $parameters['required'] = $required;
        }

        return [
            'type' => 'function',
            'name' => $this->value,
            'description' => $this->description(),
            'strict' => false,
            'parameters' => $parameters,
        ];
    }

    private function description(): string
    {
        return match ($this) {
            self::SearchLeads => 'Search visible leads by name, phone, or email. Call this before mutating when the user names a person.',
            self::GetLead => 'Get one lead the current user can access, including open follow-ups, site visits, and tasks.',
            self::ListToday => 'List overdue and due-today follow-ups, site visits, and tasks for the current user.',
            self::ScheduleFollowUp => 'Schedule a follow-up for a lead. Requires lead_id and scheduled_at.',
            self::ScheduleSiteVisit => 'Schedule a site visit. Requires lead_id, scheduled_at, property_id, and visit_type.',
            self::CompleteFollowUp => 'Mark the latest scheduled follow-up on a lead as complete.',
            self::CompleteSiteVisit => 'Mark the latest scheduled site visit on a lead as complete.',
            self::CreateTask => 'Create a task on a lead. Requires lead_id and title.',
            self::CompleteTask => 'Mark a task complete. Requires task_id.',
            self::AddNote => 'Add a note to a lead. Requires lead_id and body.',
        };
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function properties(): array
    {
        return match ($this) {
            self::SearchLeads => [
                'query' => ['type' => 'string', 'description' => 'Name, phone, or email to search for.'],
            ],
            self::GetLead => [
                'lead_id' => ['type' => 'integer', 'description' => 'Lead id from search_leads.'],
            ],
            self::ListToday => [],
            self::ScheduleFollowUp => [
                'lead_id' => ['type' => 'integer'],
                'scheduled_at' => ['type' => 'string', 'description' => 'Local datetime, e.g. 2026-09-12 16:00:00'],
                'priority' => ['type' => 'string', 'enum' => ['high', 'normal', 'low']],
                'notes' => ['type' => 'string'],
            ],
            self::ScheduleSiteVisit => [
                'lead_id' => ['type' => 'integer'],
                'scheduled_at' => ['type' => 'string', 'description' => 'Local datetime, e.g. 2026-09-12 16:00:00'],
                'property_id' => ['type' => 'integer'],
                'visit_type' => ['type' => 'string', 'enum' => ['fresh_visit', 'revisit']],
                'notes' => ['type' => 'string'],
            ],
            self::CompleteFollowUp, self::CompleteSiteVisit => [
                'lead_id' => ['type' => 'integer'],
                'notes' => ['type' => 'string'],
            ],
            self::CreateTask => [
                'lead_id' => ['type' => 'integer'],
                'title' => ['type' => 'string'],
                'description' => ['type' => 'string'],
                'due_at' => ['type' => 'string'],
            ],
            self::CompleteTask => [
                'task_id' => ['type' => 'integer'],
                'notes' => ['type' => 'string'],
            ],
            self::AddNote => [
                'lead_id' => ['type' => 'integer'],
                'body' => ['type' => 'string'],
            ],
        };
    }

    /**
     * @return list<string>
     */
    private function required(): array
    {
        return match ($this) {
            self::SearchLeads => ['query'],
            self::GetLead => ['lead_id'],
            self::ListToday => [],
            self::ScheduleFollowUp => ['lead_id', 'scheduled_at'],
            self::ScheduleSiteVisit => ['lead_id', 'scheduled_at', 'property_id', 'visit_type'],
            self::CompleteFollowUp, self::CompleteSiteVisit => ['lead_id'],
            self::CreateTask => ['lead_id', 'title'],
            self::CompleteTask => ['task_id'],
            self::AddNote => ['lead_id', 'body'],
        };
    }
}

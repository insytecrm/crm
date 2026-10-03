<?php

namespace App\Support\DataTable\Definitions;

use App\Enums\TenantPermission;
use App\Models\SalesTeam;
use App\Models\User;
use App\Support\DataTable\AbstractDataTableDefinition;

class TeamsTableDefinition extends AbstractDataTableDefinition
{
    public function key(): string
    {
        return 'teams';
    }

    /**
     * @return array<string, bool>
     */
    public function defaultColumns(): array
    {
        return [
            'name' => true,
            'manager' => true,
            'members' => true,
            'status' => true,
            'actions' => true,
        ];
    }

    /**
     * @return list<string>
     */
    public function requiredColumns(): array
    {
        return ['name'];
    }

    /**
     * @return array<string, string>
     */
    public function columnLabels(): array
    {
        return [
            'name' => __('Team Name'),
            'manager' => __('Manager'),
            'members' => __('Members'),
            'status' => __('Status'),
            'actions' => __('Actions'),
        ];
    }

    public function modelClass(): string
    {
        return SalesTeam::class;
    }

    public function bulkDeleteParameterName(): string
    {
        return 'team_ids';
    }

    public function authorizeBulkDelete(User $user): bool
    {
        return $user->hasPermission(TenantPermission::TeamsManage);
    }
}

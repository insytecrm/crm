<?php

namespace App\Support\DataTable\Definitions;

use App\Enums\TenantPermission;
use App\Models\Role;
use App\Models\User;
use App\Support\DataTable\AbstractDataTableDefinition;

class SettingsRolesTableDefinition extends AbstractDataTableDefinition
{
    public function key(): string
    {
        return 'settings_roles';
    }

    /**
     * @return array<string, bool>
     */
    public function defaultColumns(): array
    {
        return [
            'name' => true,
            'users' => true,
            'permissions' => true,
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
            'name' => __('Role'),
            'users' => __('Users'),
            'permissions' => __('Permissions'),
            'status' => __('Status'),
            'actions' => __('Actions'),
        ];
    }

    public function modelClass(): string
    {
        return Role::class;
    }

    public function bulkDeleteParameterName(): string
    {
        return 'role_ids';
    }

    public function authorizeBulkDelete(User $user): bool
    {
        return $user->hasPermission(TenantPermission::SettingsRoles);
    }
}

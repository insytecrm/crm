<?php

namespace App\Actions;

use App\Enums\TenantPermission;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class ProvisionTenantRoles
{
    public function __construct(private SyncTenantPermissions $syncTenantPermissions) {}

    public function handle(?User $assignUser = null): Role
    {
        $this->syncTenantPermissions->handle();

        $administrator = Role::query()->updateOrCreate(
            ['slug' => 'administrator'],
            [
                'name' => __('Administrator'),
                'description' => __('Full access to all current and future features.'),
                'is_system' => true,
            ],
        );

        $manager = Role::query()->updateOrCreate(
            ['slug' => 'manager'],
            [
                'name' => __('Manager'),
                'description' => __('Manage day-to-day CRM operations without user administration.'),
                'is_system' => true,
            ],
        );

        $agent = Role::query()->updateOrCreate(
            ['slug' => 'agent'],
            [
                'name' => __('Agent'),
                'description' => __('Work leads, activities, and tasks with limited admin access.'),
                'is_system' => true,
            ],
        );

        $allPermissionIds = Permission::query()->pluck('id');

        $administrator->permissions()->sync($allPermissionIds);

        $manager->permissions()->sync(
            Permission::query()
                ->whereNotIn('key', [
                    TenantPermission::SettingsUsers->value,
                    TenantPermission::SettingsRoles->value,
                    TenantPermission::TeamsManage->value,
                ])
                ->pluck('id'),
        );

        $agent->permissions()->sync(
            Permission::query()
                ->whereIn('key', [
                    TenantPermission::DashboardView->value,
                    TenantPermission::LeadsView->value,
                    TenantPermission::LeadsCreate->value,
                    TenantPermission::LeadsUpdate->value,
                    TenantPermission::ActivitiesView->value,
                    TenantPermission::ActivitiesManage->value,
                    TenantPermission::TasksView->value,
                    TenantPermission::TasksManage->value,
                    TenantPermission::PropertiesView->value,
                    TenantPermission::BookingsView->value,
                    TenantPermission::TeamChatUse->value,
                ])
                ->pluck('id'),
        );

        if ($assignUser !== null) {
            $assignUser->update(['role_id' => $administrator->id]);
        }

        User::query()
            ->whereNull('role_id')
            ->update(['role_id' => $administrator->id]);

        return $administrator;
    }
}

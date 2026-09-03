<?php

namespace App\Support\DataTable\Definitions;

use App\Enums\TenantPermission;
use App\Models\User;
use App\Support\DataTable\AbstractDataTableDefinition;

class SettingsUsersTableDefinition extends AbstractDataTableDefinition
{
    public function key(): string
    {
        return 'settings_users';
    }

    /**
     * @return array<string, bool>
     */
    public function defaultColumns(): array
    {
        return [
            'name' => true,
            'email' => true,
            'role' => true,
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
            'name' => __('Name'),
            'email' => __('Email'),
            'role' => __('Role'),
            'status' => __('Status'),
            'actions' => __('Actions'),
        ];
    }

    public function modelClass(): string
    {
        return User::class;
    }

    public function bulkDeleteParameterName(): string
    {
        return 'user_ids';
    }

    public function authorizeBulkDelete(User $user): bool
    {
        return $user->hasPermission(TenantPermission::SettingsUsers);
    }

    public function bulkDelete(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }

        $deleted = 0;
        $currentUserId = auth()->id();

        $users = User::query()->whereIn('id', $ids)->get();

        foreach ($users as $settingsUser) {
            if ($settingsUser->id === $currentUserId) {
                continue;
            }

            if ($settingsUser->isAdministrator()) {
                $adminCount = User::query()
                    ->whereHas('role', fn ($query) => $query->where('slug', 'administrator'))
                    ->where('is_active', true)
                    ->count();

                if ($adminCount <= 1) {
                    continue;
                }
            }

            $settingsUser->delete();
            $deleted++;
        }

        return $deleted;
    }
}

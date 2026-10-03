<?php

namespace App\Actions;

use App\Enums\TenantPermission;
use App\Models\Permission;

class SyncTenantPermissions
{
    public function handle(): void
    {
        foreach (TenantPermission::cases() as $index => $permission) {
            Permission::query()->updateOrCreate(
                ['key' => $permission->value],
                [
                    'label' => $permission->label(),
                    'group' => $permission->group(),
                    'description' => $permission->description(),
                    'sort_order' => $index,
                ],
            );
        }
    }
}

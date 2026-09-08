<?php

use App\Actions\SyncTenantPermissions;
use App\Enums\TenantPermission;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(SyncTenantPermissions::class)->handle();

        $permission = Permission::query()
            ->where('key', TenantPermission::ReportsView->value)
            ->first();

        if ($permission === null) {
            return;
        }

        Role::query()
            ->whereIn('slug', ['administrator', 'manager'])
            ->each(function (Role $role) use ($permission): void {
                $role->permissions()->syncWithoutDetaching([$permission->id]);
            });
    }

    public function down(): void
    {
        $permission = Permission::query()
            ->where('key', TenantPermission::ReportsView->value)
            ->first();

        if ($permission === null) {
            return;
        }

        $permission->roles()->detach();
        $permission->delete();
    }
};

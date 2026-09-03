<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\SettingsTab;
use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSettingsRoleRequest;
use App\Http\Requests\Tenant\UpdateSettingsRoleRequest;
use App\Http\Requests\Tenant\UpdateSettingsRoleStatusRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class SettingsRoleController extends Controller
{
    public function store(StoreSettingsRoleRequest $request): RedirectResponse
    {
        $role = Role::query()->create([
            'name' => $request->validated('name'),
            'slug' => $this->uniqueSlug($request->validated('name')),
            'description' => $request->validated('description'),
            'is_system' => false,
            'is_active' => true,
        ]);

        $this->syncPermissions($role, $request->validated('permissions', []));

        return redirect()
            ->route('tenant.settings.index', ['tab' => SettingsTab::Roles->value])
            ->with('status', __('Role created.'));
    }

    public function update(UpdateSettingsRoleRequest $request, Role $role): RedirectResponse
    {
        $role->update([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
        ]);

        if (! $role->isAdministrator()) {
            $this->syncPermissions($role, $request->validated('permissions', []));
        }

        return redirect()
            ->route('tenant.settings.index', ['tab' => SettingsTab::Roles->value])
            ->with('status', __('Role updated.'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::SettingsRoles), 403);

        if ($role->is_system) {
            return back()->with('status', __('System roles cannot be deleted.'));
        }

        if ($role->users()->exists()) {
            return back()->with('status', __('Remove users from this role before deleting it.'));
        }

        $role->delete();

        return redirect()
            ->route('tenant.settings.index', ['tab' => SettingsTab::Roles->value])
            ->with('status', __('Role deleted.'));
    }

    public function updateStatus(UpdateSettingsRoleStatusRequest $request, Role $role): RedirectResponse
    {
        if ($role->isAdministrator()) {
            return back()->with('status', __('The administrator role must stay active.'));
        }

        $role->update([
            'is_active' => $request->isActive(),
        ]);

        $message = $request->isActive()
            ? __('Role activated.')
            : __('Role deactivated.');

        return redirect()
            ->route('tenant.settings.index', ['tab' => SettingsTab::Roles->value])
            ->with('status', $message);
    }

    /**
     * @param  list<string>  $permissionKeys
     */
    private function syncPermissions(Role $role, array $permissionKeys): void
    {
        $permissionIds = Permission::query()
            ->whereIn('key', $permissionKeys)
            ->pluck('id');

        $role->permissions()->sync($permissionIds);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (Role::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}

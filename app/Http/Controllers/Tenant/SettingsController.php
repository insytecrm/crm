<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\SettingsTab;
use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateSettingsCompanyRequest;
use App\Http\Requests\Tenant\UpdateSettingsPasswordRequest;
use App\Http\Requests\Tenant\UpdateSettingsProfileRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Support\DataTable\DataTableViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        /** @var Tenant $company */
        $company = tenant();
        /** @var User $authUser */
        $authUser = auth()->user();
        $visibleTabs = SettingsTab::visibleFor($authUser);
        $tab = SettingsTab::fromQuery(request('tab'));

        if (! in_array($tab, $visibleTabs, true)) {
            $tab = SettingsTab::Profile;
        }

        $canManageUsers = $authUser->hasPermission(TenantPermission::SettingsUsers);
        $canManageRoles = $authUser->hasPermission(TenantPermission::SettingsRoles);

        $rolesQuery = Role::query()->orderBy('name');

        if ($canManageRoles) {
            $rolesQuery->with('permissions')->withCount(['users', 'permissions']);
        }

        $viewData = [
            'tab' => $tab,
            'visibleTabs' => $visibleTabs,
            'user' => $authUser,
            'company' => $company,
            'roles' => ($canManageUsers || $canManageRoles)
                ? $rolesQuery->get()
                : collect(),
            'permissionGroups' => $canManageRoles
                ? Permission::query()->orderBy('sort_order')->get()->groupBy('group')
                : collect(),
        ];

        if ($canManageUsers) {
            $users = User::query()->with('role')->orderBy('name')->get();
            $viewData['users'] = $users;
            $viewData = array_merge($viewData, DataTableViewData::for($authUser, 'settings_users', $users));
        } else {
            $viewData['users'] = collect();
        }

        return view('tenant.settings.index', $viewData);
    }

    public function updateProfile(UpdateSettingsProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()
            ->route('tenant.settings.index', ['tab' => SettingsTab::Profile->value])
            ->with('status', __('Profile updated.'));
    }

    public function updateCompany(UpdateSettingsCompanyRequest $request): RedirectResponse
    {
        /** @var Tenant $company */
        $company = tenant();
        $company->update($request->validated());

        return redirect()
            ->route('tenant.settings.index', ['tab' => SettingsTab::Company->value])
            ->with('status', __('Company settings updated.'));
    }

    public function updatePassword(UpdateSettingsPasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()
            ->route('tenant.settings.index', ['tab' => SettingsTab::Security->value])
            ->with('status', __('Password updated.'));
    }
}

<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\AssertPlanLimit;
use App\Enums\PlanLimitKey;
use App\Enums\SettingsTab;
use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSettingsUserRequest;
use App\Http\Requests\Tenant\UpdateSettingsUserRequest;
use App\Http\Requests\Tenant\UpdateSettingsUserStatusRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class SettingsUserController extends Controller
{
    public function store(StoreSettingsUserRequest $request): RedirectResponse
    {
        app(AssertPlanLimit::class)->handle(PlanLimitKey::Users);

        User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'password' => $request->validated('password'),
            'role_id' => $request->validated('role_id'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        return redirect()
            ->route('tenant.settings.index', ['tab' => SettingsTab::Users->value])
            ->with('status', __('User created.'));
    }

    public function update(UpdateSettingsUserRequest $request, User $user): RedirectResponse
    {
        $user->fill([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'role_id' => $request->validated('role_id'),
        ]);

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        $user->save();

        return redirect()
            ->route('tenant.settings.index', ['tab' => SettingsTab::Users->value])
            ->with('status', __('User updated.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::SettingsUsers), 403);

        if ($user->is(auth()->user())) {
            return back()->with('status', __('You cannot delete your own account.'));
        }

        if ($user->isAdministrator()) {
            $adminCount = User::query()
                ->whereHas('role', fn ($query) => $query->where('slug', 'administrator'))
                ->count();

            if ($adminCount <= 1) {
                return back()->with('status', __('At least one administrator is required.'));
            }
        }

        $user->delete();

        return redirect()
            ->route('tenant.settings.index', ['tab' => SettingsTab::Users->value])
            ->with('status', __('User deleted.'));
    }

    public function updateStatus(UpdateSettingsUserStatusRequest $request, User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('status', __('You cannot deactivate your own account.'));
        }

        if ($request->isActive() === false && $user->isAdministrator()) {
            $adminCount = User::query()
                ->where('is_active', true)
                ->whereHas('role', fn ($query) => $query->where('slug', 'administrator'))
                ->whereKeyNot($user->id)
                ->count();

            if ($adminCount === 0) {
                return back()->with('status', __('At least one active administrator is required.'));
            }
        }

        if ($request->isActive() && $user->is_active === false) {
            app(AssertPlanLimit::class)->handle(PlanLimitKey::Users);
        }

        $user->update([
            'is_active' => $request->isActive(),
        ]);

        $message = $request->isActive()
            ? __('User activated.')
            : __('User deactivated.');

        return redirect()
            ->route('tenant.settings.index', ['tab' => SettingsTab::Users->value])
            ->with('status', $message);
    }
}

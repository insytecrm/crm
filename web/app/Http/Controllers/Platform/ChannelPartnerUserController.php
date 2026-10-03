<?php

namespace App\Http\Controllers\Platform;

use App\Actions\AssertPlanLimit;
use App\Enums\PlanLimitKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\UpdateChannelPartnerUserRequest;
use App\Http\Requests\Platform\UpdateChannelPartnerUserStatusRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ChannelPartnerUserController extends Controller
{
    public function update(UpdateChannelPartnerUserRequest $request, Tenant $tenant, int $user): RedirectResponse
    {
        $tenant->run(function () use ($request, $user): void {
            $model = User::query()->findOrFail($user);

            $validated = validator($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'string',
                    'lowercase',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($model->id),
                ],
                'role_id' => [
                    'required',
                    'integer',
                    Rule::exists('roles', 'id')->where(fn ($query) => $query->where('is_active', true)),
                ],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            ])->validate();

            $model->fill([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role_id' => $validated['role_id'],
            ]);

            if (! empty($validated['password'])) {
                $model->password = $validated['password'];
            }

            $model->save();
        });

        return redirect()
            ->route('tenants.users', $tenant)
            ->with('status', __('User updated.'));
    }

    public function updateStatus(UpdateChannelPartnerUserStatusRequest $request, Tenant $tenant, int $user): RedirectResponse
    {
        $message = $tenant->run(function () use ($request, $user): string {
            $model = User::query()->with('role')->findOrFail($user);
            $isActive = $request->isActive();

            if ($isActive === false && $model->isAdministrator()) {
                $adminCount = User::query()
                    ->where('is_active', true)
                    ->whereHas('role', fn ($query) => $query->where('slug', 'administrator'))
                    ->whereKeyNot($model->id)
                    ->count();

                if ($adminCount === 0) {
                    throw ValidationException::withMessages([
                        'user' => __('At least one active administrator is required.'),
                    ]);
                }
            }

            if ($isActive && $model->is_active === false) {
                app(AssertPlanLimit::class)->handle(PlanLimitKey::Users);
            }

            $model->update([
                'is_active' => $isActive,
            ]);

            return $isActive
                ? __('User enabled.')
                : __('User disabled.');
        });

        return redirect()
            ->route('tenants.users', $tenant)
            ->with('status', $message);
    }

    public function resetAccess(Tenant $tenant, int $user): RedirectResponse
    {
        $temporaryPassword = Str::password(12);

        $tenant->run(function () use ($user, $temporaryPassword): void {
            $model = User::query()->findOrFail($user);

            $model->forceFill([
                'password' => $temporaryPassword,
                'remember_token' => null,
                'is_active' => true,
            ])->save();
        });

        return redirect()
            ->route('tenants.users', $tenant)
            ->with('status', __('Access reset. Temporary password: :password', [
                'password' => $temporaryPassword,
            ]));
    }
}

<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StorePlatformSettingsUserRequest;
use App\Http\Requests\Tenant\UpdateSettingsPasswordRequest;
use App\Http\Requests\Tenant\UpdateSettingsProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlatformSettingsController extends Controller
{
    public function index(): View
    {
        $tab = (string) request('tab', 'profile');
        $allowed = ['profile', 'security', 'users', 'roles', 'permissions'];

        if (! in_array($tab, $allowed, true)) {
            $tab = 'profile';
        }

        return view('platform.settings.index', [
            'tab' => $tab,
            'user' => auth()->user(),
            'superAdmins' => User::query()
                ->where('is_super_admin', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function updateProfile(UpdateSettingsProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()
            ->route('platform.settings', ['tab' => 'profile'])
            ->with('status', __('Profile updated.'));
    }

    public function updatePassword(UpdateSettingsPasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        return redirect()
            ->route('platform.settings', ['tab' => 'security'])
            ->with('status', __('Password updated.'));
    }

    public function storeUser(StorePlatformSettingsUserRequest $request): RedirectResponse
    {
        User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('platform.settings', ['tab' => 'users'])
            ->with('status', __('Super admin added.'));
    }

    public function destroyUser(User $user): RedirectResponse
    {
        abort_unless($user->is_super_admin, 404);

        if ($user->is(auth()->user())) {
            return redirect()
                ->route('platform.settings', ['tab' => 'users'])
                ->withErrors(['user' => __('You cannot remove your own account.')]);
        }

        if (User::query()->where('is_super_admin', true)->count() <= 1) {
            return redirect()
                ->route('platform.settings', ['tab' => 'users'])
                ->withErrors(['user' => __('At least one super admin must remain.')]);
        }

        $user->delete();

        return redirect()
            ->route('platform.settings', ['tab' => 'users'])
            ->with('status', __('Super admin removed.'));
    }
}

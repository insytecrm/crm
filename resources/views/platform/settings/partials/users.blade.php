<div id="settings-users">
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-black">{{ __('Super Admin Users') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('People who can sign in to the platform and manage Channel Partners.') }}</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-100">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Name') }}</th>
                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Email') }}</th>
                    <th class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($superAdmins as $admin)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-black">{{ $admin->name }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $admin->email }}</td>
                        <td class="px-4 py-3 text-end">
                            @if ($admin->is($user))
                                <span class="text-xs font-medium text-slate-400">{{ __('You') }}</span>
                            @else
                                <form method="POST" action="{{ route('platform.settings.users.destroy', $admin) }}" class="inline" onsubmit="return confirm(@js(__('Remove :name from super admins?', ['name' => $admin->name])))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-medium text-rose-600 hover:text-rose-700">{{ __('Remove') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500">{{ __('No super admins found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 max-w-xl">
        <h3 class="text-sm font-semibold text-black">{{ __('Add Super Admin') }}</h3>
        <form method="POST" action="{{ route('platform.settings.users.store') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <x-input-label for="new_admin_name" :value="__('Full Name')" />
                <x-text-input id="new_admin_name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
            </div>
            <div>
                <x-input-label for="new_admin_email" :value="__('Email Address')" />
                <x-text-input id="new_admin_email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
            </div>
            <div>
                <x-input-label for="new_admin_password" :value="__('Password')" />
                <x-text-input id="new_admin_password" name="password" type="password" class="mt-1 block w-full" required />
            </div>
            <div>
                <x-input-label for="new_admin_password_confirmation" :value="__('Confirm Password')" />
                <x-text-input id="new_admin_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
            </div>
            <x-ui.button type="submit" variant="default">{{ __('Add Super Admin') }}</x-ui.button>
        </form>
    </div>
</div>

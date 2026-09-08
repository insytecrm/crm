<x-app-layout :title="__('Users') . ' · ' . $tenant->name . ' | InSyte CRM'">
    <x-platform.partner-shell :tenant="$tenant" :shell="$shell">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-black">{{ __('Users') }}</h2>
                <p class="text-sm text-slate-500">{{ trans_choice(':count user|:count users', $users['total'], ['count' => number_format($users['total'])]) }}</p>
            </div>
            <x-ui.button variant="default" disabled class="pointer-events-none opacity-70">
                {{ __('+ Add User') }}
            </x-ui.button>
        </div>

        <x-input-error class="mb-4" :messages="$errors->get('user')" />

        <x-platform.panel compact>
            <form method="GET" action="{{ route('tenants.users', $tenant) }}" class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="w-full lg:max-w-md">
                    <x-auth.icon-input
                        type="search"
                        name="search"
                        value="{{ $users['filters']['search'] }}"
                        placeholder="{{ __('Search name or email...') }}"
                        onchange="this.form.submit()"
                    >
                        <x-slot:icon>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0a7 7 0 1 0-9.9-9.9 7 7 0 0 0 9.9 9.9Z" />
                            </svg>
                        </x-slot:icon>
                    </x-auth.icon-input>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <select
                        name="role"
                        class="h-8 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-black shadow-sm"
                        onchange="this.form.submit()"
                    >
                        <option value="">{{ __('Role') }}</option>
                        @foreach ($users['role_options'] as $option)
                            <option value="{{ $option['value'] }}" @selected($users['filters']['role'] === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    <select
                        name="status"
                        class="h-8 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-black shadow-sm"
                        onchange="this.form.submit()"
                    >
                        <option value="">{{ __('Status') }}</option>
                        @foreach ($users['status_options'] as $option)
                            <option value="{{ $option['value'] }}" @selected($users['filters']['status'] === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            @if (count($users['users']) === 0)
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/70 px-4 py-10 text-center text-sm text-slate-500">
                    {{ __('No users found for this channel partner.') }}
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Name') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Email') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Role') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Status') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Last active') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Created date') }}</th>
                                <th class="px-3 py-3 text-end text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($users['users'] as $user)
                                <tr>
                                    <td class="px-3 py-3 text-sm font-medium text-black">{{ $user['name'] }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $user['email'] }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $user['role'] }}</td>
                                    <td class="px-3 py-3"><x-platform.status-badge :status="$user['status']" /></td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $user['last_active_label'] }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $user['created_label'] }}</td>
                                    <td class="px-3 py-3 text-end">
                                        <x-ui.action-icon-group>
                                            @foreach ($user['actions'] as $action)
                                                @if (! empty($action['modal']) && ! ($action['disabled'] ?? false))
                                                    <x-ui.action-icon
                                                        :icon="str_contains($action['modal'], 'view-') ? 'view' : 'edit'"
                                                        type="button"
                                                        :title="$action['label']"
                                                        x-on:click="$dispatch('open-modal', @js($action['modal']))"
                                                    />
                                                @elseif (! empty($action['href']) && ($action['method'] ?? null) === 'PATCH' && ! ($action['disabled'] ?? false))
                                                    <form
                                                        method="POST"
                                                        action="{{ $action['href'] }}"
                                                        class="inline"
                                                        @if (! empty($action['confirm']))
                                                            onsubmit="return confirm(@js($action['confirm']));"
                                                        @endif
                                                    >
                                                        @csrf
                                                        @method('PATCH')
                                                        @foreach (($action['payload'] ?? []) as $payloadKey => $payloadValue)
                                                            <input type="hidden" name="{{ $payloadKey }}" value="{{ $payloadValue }}">
                                                        @endforeach
                                                        <x-ui.action-icon
                                                            :icon="($action['payload']['is_active'] ?? '1') === '0' ? 'cancel' : 'play'"
                                                            type="submit"
                                                            :title="$action['label']"
                                                        />
                                                    </form>
                                                @elseif (! empty($action['href']) && ($action['method'] ?? null) === 'POST' && ! ($action['disabled'] ?? false))
                                                    <form
                                                        method="POST"
                                                        action="{{ $action['href'] }}"
                                                        class="inline"
                                                        @if (! empty($action['confirm']))
                                                            onsubmit="return confirm(@js($action['confirm']));"
                                                        @endif
                                                    >
                                                        @csrf
                                                        <x-ui.action-icon icon="reminder" type="submit" :title="$action['label']" />
                                                    </form>
                                                @endif
                                            @endforeach
                                        </x-ui.action-icon-group>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-platform.panel>
    </x-platform.partner-shell>

    @push('modals')
        @foreach ($users['users'] as $user)
            @php
                $viewModal = 'view-partner-user-'.$user['id'];
                $editModal = 'edit-partner-user-'.$user['id'];
                $shouldOpenEdit = $errors->any() && old('_editing_partner_user') == $user['id'];
            @endphp

            <x-modal :name="$viewModal" maxWidth="lg" focusable>
                <div class="p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-black">{{ __('User Details') }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $user['name'] }}</p>
                        </div>
                        <button
                            type="button"
                            class="rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-black"
                            x-on:click="$dispatch('close-modal', '{{ $viewModal }}')"
                            aria-label="{{ __('Close') }}"
                        >
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">{{ __('Name') }}</dt>
                            <dd class="font-medium text-black">{{ $user['name'] }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">{{ __('Email') }}</dt>
                            <dd class="font-medium text-black">{{ $user['email'] }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">{{ __('Role') }}</dt>
                            <dd class="font-medium text-black">{{ $user['role'] }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">{{ __('Status') }}</dt>
                            <dd><x-platform.status-badge :status="$user['status']" /></dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">{{ __('Last active') }}</dt>
                            <dd class="font-medium text-black">{{ $user['last_active_label'] }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">{{ __('Created date') }}</dt>
                            <dd class="font-medium text-black">{{ $user['created_label'] }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6 flex justify-end gap-2">
                        <x-ui.button type="button" variant="outline" x-on:click="$dispatch('close-modal', '{{ $viewModal }}')">
                            {{ __('Close') }}
                        </x-ui.button>
                        <x-ui.button
                            type="button"
                            variant="default"
                            x-on:click="$dispatch('close-modal', '{{ $viewModal }}'); $dispatch('open-modal', '{{ $editModal }}')"
                        >
                            {{ __('Edit') }}
                        </x-ui.button>
                    </div>
                </div>
            </x-modal>

            <x-modal :name="$editModal" maxWidth="lg" :show="$shouldOpenEdit" focusable>
                <div class="p-6">
                    <h2 class="text-lg font-bold text-black">{{ __('Edit User') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $user['name'] }}</p>

                    <form method="POST" action="{{ route('tenants.users.update', [$tenant, $user['id']]) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_editing_partner_user" value="{{ $user['id'] }}">

                        <div>
                            <x-input-label :for="'partner-user-name-'.$user['id']" :value="__('Full Name')" />
                            <x-text-input
                                :id="'partner-user-name-'.$user['id']"
                                name="name"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('name', $user['name'])"
                                required
                            />
                            @if ($shouldOpenEdit)
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            @endif
                        </div>

                        <div>
                            <x-input-label :for="'partner-user-email-'.$user['id']" :value="__('Email Address')" />
                            <x-text-input
                                :id="'partner-user-email-'.$user['id']"
                                name="email"
                                type="email"
                                class="mt-1 block w-full"
                                :value="old('email', $user['email'])"
                                required
                            />
                            @if ($shouldOpenEdit)
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                            @endif
                        </div>

                        <div>
                            <x-input-label :for="'partner-user-role-'.$user['id']" :value="__('Role')" />
                            <select
                                id="partner-user-role-{{ $user['id'] }}"
                                name="role_id"
                                required
                                class="mt-1 block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm focus:border-navy focus:ring-navy"
                            >
                                @foreach ($users['edit_roles'] as $roleOption)
                                    <option value="{{ $roleOption['id'] }}" @selected((string) old('role_id', $user['role_id']) === (string) $roleOption['id'])>
                                        {{ $roleOption['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($shouldOpenEdit)
                                <x-input-error class="mt-2" :messages="$errors->get('role_id')" />
                            @endif
                        </div>

                        <div>
                            <x-input-label :for="'partner-user-password-'.$user['id']" :value="__('New Password (optional)')" />
                            <x-text-input
                                :id="'partner-user-password-'.$user['id']"
                                name="password"
                                type="password"
                                class="mt-1 block w-full"
                                autocomplete="new-password"
                            />
                            @if ($shouldOpenEdit)
                                <x-input-error class="mt-2" :messages="$errors->get('password')" />
                            @endif
                        </div>

                        <div>
                            <x-input-label :for="'partner-user-password-confirmation-'.$user['id']" :value="__('Confirm Password')" />
                            <x-text-input
                                :id="'partner-user-password-confirmation-'.$user['id']"
                                name="password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                                autocomplete="new-password"
                            />
                        </div>

                        <div class="flex justify-end gap-2">
                            <x-ui.button type="button" variant="outline" x-on:click="$dispatch('close-modal', '{{ $editModal }}')">
                                {{ __('Cancel') }}
                            </x-ui.button>
                            <x-ui.button type="submit" variant="default">
                                {{ __('Save User') }}
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </x-modal>
        @endforeach
    @endpush
</x-app-layout>

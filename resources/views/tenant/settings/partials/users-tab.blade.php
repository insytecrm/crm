@php
    use App\Enums\SettingsTab;
@endphp

@include('tenant.partials.manageable-table-setup')

<x-tenant.manageable-table.wrapper
    :data-table-key="$dataTableKey"
    :data-table-item-ids="$dataTableItemIds"
    :data-table-custom-values="$dataTableCustomValues"
>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-black">{{ SettingsTab::Users->label() }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ SettingsTab::Users->description() }}</p>
            </div>
            <div class="flex items-center gap-2">
                <x-tenant.manageable-table.toolbar-button :data-table-key="$dataTableKey" />
                <x-ui.button type="button" variant="default" @click="$dispatch('open-modal', 'create-settings-user')">
                    {{ __('Add User') }}
                </x-ui.button>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-100">
            <x-tenant.manageable-table.bulk-bar
                :data-table-can-bulk-delete="$dataTableCanBulkDelete"
                :data-table-bulk-delete-url="$dataTableBulkDeleteUrl"
                :data-table-bulk-delete-param="$dataTableBulkDeleteParam"
                :confirm-message="__('Are you sure you want to delete the selected users?')"
            />

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr class="align-middle">
                            <x-tenant.manageable-table.checkbox-header />
                            <th x-show="isColumnVisible('name')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Name') }}</th>
                            <th x-show="isColumnVisible('email')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Email') }}</th>
                            <th x-show="isColumnVisible('role')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Role') }}</th>
                            <th x-show="isColumnVisible('status')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Status') }}</th>
                            <x-tenant.manageable-table.custom-column-headers />
                            <th x-show="isColumnVisible('actions')" class="whitespace-nowrap px-4 py-3 text-end align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($users as $settingsUser)
                            <tr @class([
                                'align-middle transition hover:bg-slate-50/60',
                                'bg-slate-50/50' => ! $settingsUser->isActive(),
                            ])>
                                <x-tenant.manageable-table.checkbox-cell :id="$settingsUser->id" />
                                <td x-show="isColumnVisible('name')" class="whitespace-nowrap px-4 py-3 align-middle text-sm font-medium text-black">
                                    {{ $settingsUser->name }}
                                </td>
                                <td x-show="isColumnVisible('email')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $settingsUser->email }}</td>
                                <td x-show="isColumnVisible('role')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $settingsUser->role?->name ?? '—' }}</td>
                                <td x-show="isColumnVisible('status')" class="whitespace-nowrap px-4 py-3 align-middle text-sm">
                                    @if ($settingsUser->isActive())
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">{{ __('Active') }}</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <x-tenant.manageable-table.custom-column-cells :record-id="$settingsUser->id" />
                                <td x-show="isColumnVisible('actions')" class="whitespace-nowrap px-4 py-3 align-middle text-end">
                                    @include('tenant.settings.partials.user-action-icons', [
                                        'settingsUser' => $settingsUser,
                                        'currentUser' => $user,
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="20" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('No users yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-tenant.manageable-table.wrapper>

@push('modals')
    <x-tenant.manageable-table.edit-columns-modal
        :data-table-key="$dataTableKey"
        :data-table-column-labels="$dataTableColumnLabels"
        :data-table-required-columns="$dataTableRequiredColumns"
    />

    @include('tenant.settings.partials.user-form-modal', [
        'modalName' => 'create-settings-user',
        'title' => __('Add User'),
        'action' => route('tenant.settings.users.store'),
        'method' => 'POST',
        'settingsUser' => null,
        'roles' => $roles,
    ])

    @foreach ($users as $settingsUser)
        @include('tenant.settings.partials.user-form-modal', [
            'modalName' => 'edit-settings-user-'.$settingsUser->id,
            'title' => __('Edit User'),
            'action' => route('tenant.settings.users.update', $settingsUser),
            'method' => 'PATCH',
            'settingsUser' => $settingsUser,
            'roles' => $roles,
        ])
    @endforeach
@endpush

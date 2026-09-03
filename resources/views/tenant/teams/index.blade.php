<x-tenant-layout :title="__('Teams') . ' | InSyte CRM'">
    @include('tenant.partials.manageable-table-setup')

    <x-tenant.manageable-table.wrapper
        :data-table-key="$dataTableKey"
        :data-table-item-ids="$dataTableItemIds"
        :data-table-custom-values="$dataTableCustomValues"
    >
        <div
            @if ($openCreateTeam)
                x-data
                x-init="$nextTick(() => $dispatch('open-modal', 'create-team'))"
            @endif
        >
            <x-auth-session-status class="mb-3 rounded-lg bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700" :status="session('status')" />

            <x-tenant.list-toolbar>
                <x-slot:search>
                    <form method="GET" action="{{ route('tenant.teams.index') }}" class="w-full">
                        <x-auth.icon-input
                            type="search"
                            name="search"
                            value="{{ $search }}"
                            placeholder="{{ __('Search by team name or manager...') }}"
                        >
                            <x-slot:icon>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                            </x-slot:icon>
                        </x-auth.icon-input>
                    </form>
                </x-slot:search>

                @if ($canManage)
                    <x-ui.button type="button" variant="default" @click.stop="$dispatch('open-modal', 'create-team')">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        {{ __('Add Team') }}
                    </x-ui.button>
                @endif
                <x-tenant.manageable-table.toolbar-button :data-table-key="$dataTableKey" />
            </x-tenant.list-toolbar>

            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                <x-tenant.manageable-table.bulk-bar
                    :data-table-can-bulk-delete="$dataTableCanBulkDelete"
                    :data-table-bulk-delete-url="$dataTableBulkDeleteUrl"
                    :data-table-bulk-delete-param="$dataTableBulkDeleteParam"
                    :confirm-message="__('Are you sure you want to delete the selected teams?')"
                />

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr class="align-middle">
                                <x-tenant.manageable-table.checkbox-header />
                                <th x-show="isColumnVisible('name')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Team Name') }}</th>
                                <th x-show="isColumnVisible('manager')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Manager') }}</th>
                                <th x-show="isColumnVisible('members')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Members') }}</th>
                                <th x-show="isColumnVisible('status')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Status') }}</th>
                                <x-tenant.manageable-table.custom-column-headers />
                                <th x-show="isColumnVisible('actions')" class="whitespace-nowrap px-4 py-3 text-end align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($teams as $team)
                                <tr @class([
                                    'align-middle transition hover:bg-slate-50/60',
                                    'bg-slate-50/50' => ! $team->isActive(),
                                ])>
                                    <x-tenant.manageable-table.checkbox-cell :id="$team->id" />
                                    <td x-show="isColumnVisible('name')" class="whitespace-nowrap px-4 py-3 align-middle text-sm font-medium text-black">
                                        <a href="{{ route('tenant.teams.show', $team) }}" class="hover:text-black/80 hover:underline">
                                            {{ $team->name }}
                                        </a>
                                    </td>
                                    <td x-show="isColumnVisible('manager')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">
                                        {{ $team->manager?->name ?? '—' }}
                                    </td>
                                    <td x-show="isColumnVisible('members')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">
                                        {{ $team->members_count }}
                                    </td>
                                    <td x-show="isColumnVisible('status')" class="whitespace-nowrap px-4 py-3 align-middle text-sm">
                                        @if ($team->isActive())
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">{{ __('Active') }}</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <x-tenant.manageable-table.custom-column-cells :record-id="$team->id" />
                                    <td x-show="isColumnVisible('actions')" class="whitespace-nowrap px-4 py-3 align-middle text-end">
                                        @include('tenant.teams.partials.team-action-icons', [
                                            'team' => $team,
                                            'canManage' => $canManage,
                                            'editInModal' => false,
                                        ])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="20" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('No teams yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($teams->hasPages())
                <div class="mt-4">{{ $teams->links() }}</div>
            @endif
        </div>
    </x-tenant.manageable-table.wrapper>

    @push('modals')
        <x-tenant.manageable-table.edit-columns-modal
            :data-table-key="$dataTableKey"
            :data-table-column-labels="$dataTableColumnLabels"
            :data-table-required-columns="$dataTableRequiredColumns"
        />

        @if ($canManage)
            @include('tenant.teams.partials.team-form-modal', [
                'modalName' => 'create-team',
                'title' => __('Add Team'),
                'action' => route('tenant.teams.store'),
                'method' => 'POST',
                'team' => null,
                'managers' => $managers,
            ])
        @endif
    @endpush
</x-tenant-layout>

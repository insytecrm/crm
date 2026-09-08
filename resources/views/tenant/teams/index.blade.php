<x-tenant-layout :title="__('Teams') . ' | InSyte CRM'">
    @include('tenant.partials.manageable-table-setup')

    <x-tenant.manageable-table.wrapper
        :data-table-key="$dataTableKey"
        :data-table-item-ids="$dataTableItemIds"
        :data-table-custom-values="$dataTableCustomValues"
    >
        <div
            x-data
            @if ($openCreateTeam)
                x-init="$nextTick(() => $dispatch('open-modal', 'create-team'))"
            @elseif ($openCreateRouting)
                x-init="$nextTick(() => $dispatch('open-modal', 'add-routing'))"
            @elseif (filled($openEditRoutingId))
                x-init="$nextTick(() => $dispatch('open-modal', @js('edit-routing-'.$openEditRoutingId)))"
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
                    <x-ui.button type="button" variant="outline" @click.stop="$dispatch('open-modal', 'add-routing')">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                        {{ __('Add Routing') }}
                    </x-ui.button>
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

            <div class="mt-8">
                <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-black">{{ __('Lead Routing') }}</h2>
                        <p class="text-sm text-slate-500">{{ __('Decide which team gets leads from each source. Automations handle what happens next.') }}</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50">
                                <tr class="align-middle">
                                    <th class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Source') }}</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Team') }}</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Members') }}</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Distribution') }}</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Status') }}</th>
                                    @if ($canManage)
                                        <th class="whitespace-nowrap px-4 py-3 text-end align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Actions') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($routingRules as $rule)
                                    @php
                                        $sourceEnum = \App\Enums\LeadSource::tryFrom($rule->source);
                                    @endphp
                                    <tr @class([
                                        'align-middle transition hover:bg-slate-50/60',
                                        'bg-slate-50/50' => ! $rule->isActive(),
                                    ])>
                                        <td class="px-4 py-3 align-middle text-sm font-medium text-black">
                                            <div>{{ $sourceEnum?->label() ?? $rule->source }}</div>
                                            @if (filled($rule->sub_source))
                                                <div class="mt-0.5 text-xs font-normal text-slate-500">{{ $rule->sub_source }}</div>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $rule->team?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 align-middle text-sm text-slate-600">
                                            {{ $rule->members->pluck('name')->join(', ') ?: '—' }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">
                                            {{ $rule->distribution->label() }}
                                            @if ($rule->distribution->value === 'custom')
                                                <span class="mt-0.5 block text-xs text-slate-400">
                                                    {{ $rule->members->map(fn ($member) => $member->name.': '.(int) ($member->pivot->weight ?? 1))->join(' · ') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 align-middle text-sm">
                                            @if ($rule->isActive())
                                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">{{ __('Active') }}</span>
                                            @else
                                                <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        @if ($canManage)
                                            <td class="whitespace-nowrap px-4 py-3 align-middle text-end">
                                                <div class="inline-flex items-center gap-1.5">
                                                    <button
                                                        type="button"
                                                        class="inline-flex size-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600 shadow-sm ring-1 ring-slate-600/10 transition hover:bg-slate-200"
                                                        title="{{ __('Edit') }}"
                                                        aria-label="{{ __('Edit') }}"
                                                        @click="$dispatch('open-modal', @js('edit-routing-'.$rule->id))"
                                                    >
                                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                                        </svg>
                                                    </button>
                                                    <form
                                                        method="POST"
                                                        action="{{ route('tenant.teams.routing-rules.destroy', $rule) }}"
                                                        onsubmit="return confirm(@js(__('Delete this routing rule?')))"
                                                        class="inline"
                                                    >
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            type="submit"
                                                            title="{{ __('Delete') }}"
                                                            aria-label="{{ __('Delete') }}"
                                                            class="inline-flex size-8 items-center justify-center rounded-lg bg-rose-100 text-rose-600 shadow-sm ring-1 ring-rose-600/15 transition hover:bg-rose-200"
                                                        >
                                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $canManage ? 6 : 5 }}" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('No routing rules yet. Add routing to send source leads to a team automatically.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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

        @if ($canManage)
            @include('tenant.teams.partials.team-form-modal', [
                'modalName' => 'create-team',
                'title' => __('Add Team'),
                'action' => route('tenant.teams.store'),
                'method' => 'POST',
                'team' => null,
                'managers' => $managers,
            ])

            @include('tenant.teams.partials.routing-form-modal', [
                'modalName' => 'add-routing',
                'action' => route('tenant.teams.routing-rules.store'),
                'method' => 'POST',
                'rule' => null,
                'teams' => $routingTeams,
                'sources' => $routingSources,
                'distributions' => $routingDistributions,
                'subSources' => $routingSubSources,
            ])

            @foreach ($routingRules as $rule)
                @include('tenant.teams.partials.routing-form-modal', [
                    'modalName' => 'edit-routing-'.$rule->id,
                    'action' => route('tenant.teams.routing-rules.update', $rule),
                    'method' => 'PATCH',
                    'rule' => $rule,
                    'teams' => $routingTeams,
                    'sources' => $routingSources,
                    'distributions' => $routingDistributions,
                    'subSources' => $routingSubSources,
                ])
            @endforeach
        @endif
    @endpush
</x-tenant-layout>

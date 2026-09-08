@props([
    'title',
    'description',
    'stage',
    'stages',
    'activities',
    'statistics',
    'search',
    'indexRoute',
    'activityLabel',
    'isFollowUpList' => false,
])

<x-tenant-layout :title="$title . ' | InSyte CRM'">
    @include('tenant.partials.manageable-table-setup')

    <x-tenant.manageable-table.wrapper
        :data-table-key="$dataTableKey"
        :data-table-item-ids="$dataTableItemIds"
        :data-table-custom-values="$dataTableCustomValues"
    >
        @include('tenant.activities.partials.stage-stat-cards', [
            'stage' => $stage,
            'stages' => $stages,
            'statistics' => $statistics,
            'indexRoute' => $indexRoute,
            'search' => $search,
        ])

        <x-tenant.list-toolbar>
            <x-slot:search>
                <form method="GET" action="{{ route($indexRoute) }}" class="w-full">
                    <input type="hidden" name="stage" value="{{ $stage->value }}">
                    <x-auth.icon-input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="{{ __('Search by name, phone, or email...') }}"
                    >
                        <x-slot:icon>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                        </x-slot:icon>
                    </x-auth.icon-input>
                </form>
            </x-slot:search>

            <x-tenant.manageable-table.toolbar-button :data-table-key="$dataTableKey" />
        </x-tenant.list-toolbar>

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                <h2 class="text-sm font-semibold text-black">{{ $stage->listHeading($activityLabel) }}</h2>
            </div>

            <x-tenant.manageable-table.bulk-bar
                :data-table-can-bulk-delete="$dataTableCanBulkDelete"
                :data-table-bulk-delete-url="$dataTableBulkDeleteUrl"
                :data-table-bulk-delete-param="$dataTableBulkDeleteParam"
                :confirm-message="__('Are you sure you want to delete the selected activities?')"
            />

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr class="align-middle">
                            <x-tenant.manageable-table.checkbox-header />
                            <th x-show="isColumnVisible('lead')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Lead') }}</th>
                            <th x-show="isColumnVisible('phone')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Phone') }}</th>
                            <th x-show="isColumnVisible('assigned_to')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Assigned To') }}</th>
                            <th x-show="isColumnVisible('activity')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Activity') }}</th>
                            @unless ($isFollowUpList ?? false)
                                <th x-show="isColumnVisible('property')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Property') }}</th>
                            @endunless
                            <th x-show="isColumnVisible('priority')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Priority') }}</th>
                            <th x-show="isColumnVisible('scheduled')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Scheduled') }}</th>
                            <th x-show="isColumnVisible('completion_method')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $dataTableColumnLabels['completion_method'] ?? __('Method') }}</th>
                            <th x-show="isColumnVisible('completion_outcome')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Outcome') }}</th>
                            <th x-show="isColumnVisible('next_step')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Next step') }}</th>
                            <th x-show="isColumnVisible('stage')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Stage') }}</th>
                            <x-tenant.manageable-table.custom-column-headers />
                            <th x-show="isColumnVisible('actions')" class="whitespace-nowrap px-4 py-3 text-end align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($activities as $activity)
                            @include('tenant.activities.partials.scheduled-activity-row', [
                                'activity' => $activity,
                                'showPropertyColumn' => ! ($isFollowUpList ?? false),
                            ])
                        @empty
                            <tr>
                                <td colspan="20" class="px-4 py-12 text-center text-sm text-slate-500">
                                    {{ $stage->emptyMessage($activityLabel) }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($activities->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">{{ $activities->links() }}</div>
            @endif
        </div>
    </x-tenant.manageable-table.wrapper>

    @push('modals')
        <x-tenant.manageable-table.edit-columns-modal
            :data-table-key="$dataTableKey"
            :data-table-column-labels="$dataTableColumnLabels"
            :data-table-required-columns="$dataTableRequiredColumns"
        />

        @include('tenant.activities.partials.activity-modals', ['activities' => $activities])
    @endpush
</x-tenant-layout>

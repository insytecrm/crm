<x-tenant-layout :title="__('Activities') . ' | InSyte CRM'">
    @include('tenant.partials.manageable-table-setup')

    <x-tenant.manageable-table.wrapper
        :data-table-key="$dataTableKey"
        :data-table-item-ids="$dataTableItemIds"
        :data-table-custom-values="$dataTableCustomValues"
    >
        @php
            $cardQuery = fn (?string $kindValue = null) => array_filter([
                'kind' => $kindValue,
            ]);
        @endphp

        <div class="mb-3 flex justify-end gap-2">
            <x-tenant.manageable-table.toolbar-button :data-table-key="$dataTableKey" />
        </div>

        {{-- Summary cards --}}
        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 sm:items-stretch lg:grid-cols-3">
            <x-tenant.stat-card
                :label="__('Total Activities')"
                :value="$statistics['total_activities']"
                accent="navy"
                :href="route('tenant.activities.index', $cardQuery())"
                :active="$kind === null"
            >
                <x-slot:icon>
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                :label="__('Site Visits')"
                :value="$statistics['site_visits']"
                accent="emerald"
                :href="route('tenant.activities.index', $cardQuery('site_visit'))"
                :active="$kind?->value === 'site_visit'"
            >
                <x-slot:icon>
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                :label="__('Follow-ups')"
                :value="$statistics['follow_ups']"
                accent="sky"
                :href="route('tenant.activities.index', $cardQuery('follow_up'))"
                :active="$kind?->value === 'follow_up'"
            >
                <x-slot:icon>
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>
        </div>

        <x-auth-session-status class="mb-3 rounded-lg bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700" :status="session('status')" />

        {{-- Activity list --}}
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                <h2 class="text-sm font-semibold text-black">{{ $filter->listHeading() }}</h2>
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
                            <th x-show="isColumnVisible('time')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Time') }}</th>
                            <th x-show="isColumnVisible('activity')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Activity') }}</th>
                            <th x-show="isColumnVisible('lead')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Lead') }}</th>
                            @if ($showPropertyColumn ?? false)
                                <th x-show="isColumnVisible('property')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Property') }}</th>
                            @endif
                            <th x-show="isColumnVisible('notes')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Notes') }}</th>
                            <th x-show="isColumnVisible('status')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Status') }}</th>
                            <x-tenant.manageable-table.custom-column-headers />
                            <th x-show="isColumnVisible('actions')" class="whitespace-nowrap px-4 py-3 text-end align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($activities as $activity)
                            @include('tenant.activities.partials.activity-item', [
                                'activity' => $activity,
                                'showPropertyColumn' => $showPropertyColumn ?? false,
                            ])
                        @empty
                            <tr>
                                <td colspan="20" class="px-4 py-12 text-center text-sm text-slate-500">
                                    {{ $filter->emptyMessage() }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if (session('external_redirect'))
            <div
                x-data
                x-init="
                    const url = @js(session('external_redirect'));
                    if (url.startsWith('tel:')) {
                        window.location.href = url;
                    } else {
                        window.open(url, '_blank');
                    }
                "
                class="hidden"
                aria-hidden="true"
            ></div>
        @endif
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

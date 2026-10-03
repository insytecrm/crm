<x-tenant-layout :title="__('Tasks') . ' | InSyte CRM'">
    @include('tenant.partials.manageable-table-setup')

    <x-tenant.manageable-table.wrapper
        :data-table-key="$dataTableKey"
        :data-table-item-ids="$dataTableItemIds"
        :data-table-custom-values="$dataTableCustomValues"
    >
        {{-- Summary cards --}}
        <div class="mb-4 flex gap-2">
            <x-tenant.stat-card
                comfortable
                :label="__('Total Tasks')"
                :value="$statistics['total']"
                accent="navy"
                :href="route('tenant.tasks.index', ['filter' => 'all'])"
                :active="$filter === \App\Enums\TaskFilter::All"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm0 5.25h.007v.008H3.75v-.008Zm0 5.25h.007v.008H3.75v-.008Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                comfortable
                :label="__('Pending')"
                :value="$statistics['pending']"
                accent="amber"
                :href="route('tenant.tasks.index', ['filter' => 'pending'])"
                :active="$filter === \App\Enums\TaskFilter::Pending"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                comfortable
                :label="__('In Progress')"
                :value="$statistics['in_progress']"
                accent="sky"
                :href="route('tenant.tasks.index', ['filter' => 'in_progress'])"
                :active="$filter === \App\Enums\TaskFilter::InProgress"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                comfortable
                :label="__('Completed')"
                :value="$statistics['completed']"
                accent="emerald"
                :href="route('tenant.tasks.index', ['filter' => 'completed'])"
                :active="$filter === \App\Enums\TaskFilter::Completed"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                comfortable
                :label="__('Cancelled')"
                :value="$statistics['cancelled']"
                accent="rose"
                :href="route('tenant.tasks.index', ['filter' => 'cancelled'])"
                :active="$filter === \App\Enums\TaskFilter::Cancelled"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>
        </div>

        <x-tenant.list-toolbar>
            <x-slot:search>
                <form method="GET" action="{{ route('tenant.tasks.index') }}" class="w-full">
                    <input type="hidden" name="filter" value="{{ $filter->value }}">
                    <x-auth.icon-input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="{{ __('Search by task, lead, or assignee...') }}"
                    >
                        <x-slot:icon>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                        </x-slot:icon>
                    </x-auth.icon-input>
                </form>
            </x-slot:search>

            <x-ui.button type="button" variant="default" @click="$dispatch('open-modal', 'create-task')">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                {{ __('Create Task') }}
            </x-ui.button>
            <x-tenant.manageable-table.toolbar-button :data-table-key="$dataTableKey" />
        </x-tenant.list-toolbar>

        <x-auth-session-status class="mb-3 rounded-lg bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700" :status="session('status')" />

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                <h2 class="text-sm font-semibold text-black">{{ $filter->listHeading() }}</h2>
            </div>

            <x-tenant.manageable-table.bulk-bar
                :data-table-can-bulk-delete="$dataTableCanBulkDelete"
                :data-table-bulk-delete-url="$dataTableBulkDeleteUrl"
                :data-table-bulk-delete-param="$dataTableBulkDeleteParam"
                :confirm-message="__('Are you sure you want to delete the selected tasks?')"
            />

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr class="align-middle">
                            <x-tenant.manageable-table.checkbox-header />
                            <th x-show="isColumnVisible('title')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Task') }}</th>
                            <th x-show="isColumnVisible('related_to')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Related To') }}</th>
                            <th x-show="isColumnVisible('assigned_to')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Assigned To') }}</th>
                            <th x-show="isColumnVisible('due_date')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Due Date') }}</th>
                            <th x-show="isColumnVisible('status')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Status') }}</th>
                            <th x-show="isColumnVisible('description')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Description') }}</th>
                            <x-tenant.manageable-table.custom-column-headers />
                            <th x-show="isColumnVisible('actions')" class="whitespace-nowrap px-4 py-3 text-end align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($tasks as $task)
                            @include('tenant.tasks.partials.task-row', ['task' => $task, 'filter' => $filter])
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
    </x-tenant.manageable-table.wrapper>

    @push('modals')
        <x-tenant.manageable-table.edit-columns-modal
            :data-table-key="$dataTableKey"
            :data-table-column-labels="$dataTableColumnLabels"
            :data-table-required-columns="$dataTableRequiredColumns"
        />

        @include('tenant.tasks.partials.create-task-modal', [
            'leads' => $leads,
            'users' => $users,
        ])

        @foreach ($tasks as $task)
            @include('tenant.tasks.partials.task-details-modal', ['task' => $task, 'filter' => $filter])
        @endforeach
    @endpush
</x-tenant-layout>

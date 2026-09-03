<x-tenant-layout :title="__('Tasks') . ' | InSyte CRM'">
    @include('tenant.partials.manageable-table-setup')

    <x-tenant.manageable-table.wrapper
        :data-table-key="$dataTableKey"
        :data-table-item-ids="$dataTableItemIds"
        :data-table-custom-values="$dataTableCustomValues"
    >
        <div class="mb-3 flex justify-end gap-2">
            <x-tenant.manageable-table.toolbar-button :data-table-key="$dataTableKey" />
            <x-ui.button type="button" variant="default" @click="$dispatch('open-modal', 'create-task')">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                {{ __('Create Task') }}
            </x-ui.button>
        </div>

        {{-- Filter tags --}}
        <div class="mb-4 flex flex-wrap items-center gap-1 text-sm font-medium">
            @foreach ($filters as $filterOption)
                <a
                    href="{{ route('tenant.tasks.index', ['filter' => $filterOption->value]) }}"
                    @class([
                        'rounded-full px-3 py-1 transition-colors',
                        'bg-navy text-white' => $filter === $filterOption,
                        'text-slate-600 hover:bg-slate-100 hover:text-black' => $filter !== $filterOption,
                    ])
                >
                    {{ $filterOption->label() }}
                </a>
                @if (! $loop->last)
                    <span class="text-slate-300" aria-hidden="true">|</span>
                @endif
            @endforeach
        </div>

        {{-- Summary cards --}}
        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 sm:items-stretch lg:grid-cols-4">
            <x-tenant.stat-card
                :label="__('Today')"
                :value="$statistics['today']"
                accent="navy"
                :href="route('tenant.tasks.index', ['filter' => 'today'])"
                :active="$filter === \App\Enums\TaskFilter::Today"
            >
                <x-slot:icon>
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                :label="__('Upcoming')"
                :value="$statistics['upcoming']"
                accent="sky"
                :href="route('tenant.tasks.index', ['filter' => 'upcoming'])"
                :active="$filter === \App\Enums\TaskFilter::Upcoming"
            >
                <x-slot:icon>
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                :label="__('Completed')"
                :value="$statistics['completed']"
                accent="emerald"
                :href="route('tenant.tasks.index', ['filter' => 'completed'])"
                :active="$filter === \App\Enums\TaskFilter::Completed"
            >
                <x-slot:icon>
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>

            <x-tenant.stat-card
                :label="__('All')"
                :value="$statistics['all']"
                accent="amber"
                :href="route('tenant.tasks.index', ['filter' => 'all'])"
                :active="$filter === \App\Enums\TaskFilter::All"
            >
                <x-slot:icon>
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm0 5.25h.007v.008H3.75v-.008Zm0 5.25h.007v.008H3.75v-.008Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>
        </div>

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

@php
    $filterIsActive = $filter->isActive();
@endphp

<x-tenant-layout :title="__('Invoices') . ' | InSyte CRM'">
    @include('tenant.partials.manageable-table-setup')

    <x-tenant.manageable-table.wrapper
        :data-table-key="$dataTableKey"
        :data-table-item-ids="$dataTableItemIds"
        :data-table-custom-values="$dataTableCustomValues"
    >
        <div
            x-data="{ filtersOpen: @js($filterIsActive) }"
            @if ($openModal)
                x-init="$nextTick(() => $dispatch('open-modal', @js($openModal)))"
            @endif
        >
            <x-tenant.list-toolbar>
                <x-slot:search>
                    <form method="GET" action="{{ route('tenant.invoices.index') }}" class="w-full">
                        @if ($filter->isActive())
                            <input type="hidden" name="payment" value="{{ $filter->value }}">
                        @endif
                        <x-auth.icon-input
                            type="search"
                            name="search"
                            value="{{ $search }}"
                            placeholder="{{ __('Search by invoice, lead, property, or unit...') }}"
                        >
                            <x-slot:icon>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                            </x-slot:icon>
                        </x-auth.icon-input>
                    </form>
                </x-slot:search>

                <x-ui.button type="button" variant="default" @click="$dispatch('open-modal', 'create-invoice')">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('Create Invoice') }}
                </x-ui.button>
                <x-ui.button
                    type="button"
                    variant="soft"
                    @click="filtersOpen = !filtersOpen"
                    x-bind:class="filtersOpen && 'ring-2 ring-navy/20'"
                    x-bind:aria-expanded="filtersOpen"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                    </svg>
                    {{ __('Filters') }}
                    @if ($filterIsActive)
                        <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-navy px-1.5 py-0.5 text-[10px] font-bold text-white">1</span>
                    @endif
                    <svg
                        class="h-4 w-4 transition-transform duration-200"
                        :class="filtersOpen && 'rotate-180'"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </x-ui.button>
                <x-tenant.manageable-table.toolbar-button :data-table-key="$dataTableKey" />

                <x-slot:panel>
                    @include('tenant.invoices.partials.list-filters-panel', ['filter' => $filter, 'search' => $search])
                </x-slot:panel>
            </x-tenant.list-toolbar>

            <x-auth-session-status class="mb-3 rounded-lg bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700" :status="session('status')" />

            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                    <h2 class="text-sm font-semibold text-black">{{ __('All Invoices') }}</h2>
                </div>

                <x-tenant.manageable-table.bulk-bar
                    :data-table-can-bulk-delete="$dataTableCanBulkDelete"
                    :data-table-bulk-delete-url="$dataTableBulkDeleteUrl"
                    :data-table-bulk-delete-param="$dataTableBulkDeleteParam"
                    :confirm-message="__('Are you sure you want to delete the selected invoices?')"
                />

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr class="align-middle">
                                <x-tenant.manageable-table.checkbox-header />
                                <th x-show="isColumnVisible('invoice_number')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Invoice Number') }}</th>
                                <th x-show="isColumnVisible('lead')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Lead Name') }}</th>
                                <th x-show="isColumnVisible('property')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Property') }}</th>
                                <th x-show="isColumnVisible('unit')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Unit Number') }}</th>
                                <th x-show="isColumnVisible('agreement_value')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Agreement Value') }}</th>
                                <th x-show="isColumnVisible('invoice_amount')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Invoice Amount') }}</th>
                                <th x-show="isColumnVisible('agreement_date')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Agreement Date') }}</th>
                                <th x-show="isColumnVisible('invoice_date')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Invoice Date') }}</th>
                                <th x-show="isColumnVisible('payment_status')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Payment Status') }}</th>
                                <x-tenant.manageable-table.custom-column-headers />
                                <th x-show="isColumnVisible('actions')" class="whitespace-nowrap px-4 py-3 text-end align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($invoices as $invoice)
                                @include('tenant.invoices.partials.invoice-row', ['invoice' => $invoice])
                            @empty
                                <tr>
                                    <td colspan="20" class="px-4 py-12 text-center text-sm text-slate-500">
                                        {{ $filter->isActive() ? __('No invoices match the selected filters.') : __('No invoices yet.') }}
                                    </td>
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

        @include('tenant.invoices.partials.create-invoice-modal', ['billableBookings' => $billableBookings])

        @foreach ($invoices as $invoice)
            @include('tenant.invoices.partials.invoice-details-modal', ['invoice' => $invoice])
        @endforeach
    @endpush
</x-tenant-layout>

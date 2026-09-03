<x-tenant-layout :title="__('Invoices') . ' | InSyte CRM'">
    @include('tenant.partials.manageable-table-setup')

    <x-tenant.manageable-table.wrapper
        :data-table-key="$dataTableKey"
        :data-table-item-ids="$dataTableItemIds"
        :data-table-custom-values="$dataTableCustomValues"
    >
        <div
            @if ($openModal)
                x-data
                x-init="$nextTick(() => $dispatch('open-modal', @js($openModal)))"
            @endif
        >
            <div class="mb-3 flex justify-end gap-2">
                <x-tenant.manageable-table.toolbar-button :data-table-key="$dataTableKey" />
            </div>

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
                                <th x-show="isColumnVisible('property')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Property') }}</th>
                                <th x-show="isColumnVisible('invoice_number')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Invoice #') }}</th>
                                <th x-show="isColumnVisible('unit')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Unit') }}</th>
                                <th x-show="isColumnVisible('lead')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Lead') }}</th>
                                <th x-show="isColumnVisible('invoice_amount')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Invoice Amount') }}</th>
                                <th x-show="isColumnVisible('invoice_date')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Invoice Date') }}</th>
                                <th x-show="isColumnVisible('agreement_date')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Agreement Date') }}</th>
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
                                        {{ __('No invoices yet.') }}
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

        @foreach ($invoices as $invoice)
            @include('tenant.invoices.partials.invoice-details-modal', ['invoice' => $invoice])
        @endforeach
    @endpush
</x-tenant-layout>

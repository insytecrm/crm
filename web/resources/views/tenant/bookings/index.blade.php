<x-tenant-layout :title="__('Bookings') . ' | InSyte CRM'">
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
            <div class="mb-4 flex gap-2">
                <x-tenant.stat-card
                    comfortable
                    :label="__('Total Bookings')"
                    :value="number_format($statistics['total'])"
                    accent="navy"
                >
                    <x-slot:icon>
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m-13.125 3h18.375A2.625 2.625 0 0 0 21 18.375V5.625A2.625 2.625 0 0 0 18.375 3H5.625A2.625 2.625 0 0 0 3 5.625v12.75A2.625 2.625 0 0 0 5.625 21Z" />
                        </svg>
                    </x-slot:icon>
                </x-tenant.stat-card>

                <x-tenant.stat-card
                    comfortable
                    :label="__('Agreement Done')"
                    :value="number_format($statistics['agreement_done'])"
                    accent="emerald"
                >
                    <x-slot:icon>
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </x-slot:icon>
                </x-tenant.stat-card>

                <x-tenant.stat-card
                    comfortable
                    :label="__('Invoice Created')"
                    :value="number_format($statistics['invoice_created'])"
                    accent="sky"
                >
                    <x-slot:icon>
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </x-slot:icon>
                </x-tenant.stat-card>

                <x-tenant.stat-card
                    comfortable
                    :label="__('Booking Value')"
                    :value="'₹'.number_format($statistics['booking_value'])"
                    accent="amber"
                >
                    <x-slot:icon>
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 8.25H9m6 3H9m3 6-3-3h1.5a3 3 0 1 0 0-6M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </x-slot:icon>
                </x-tenant.stat-card>

                <x-tenant.stat-card
                    comfortable
                    :label="__('Bookings This Month')"
                    :value="number_format($statistics['bookings_this_month'])"
                    accent="cyan"
                >
                    <x-slot:icon>
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </x-slot:icon>
                </x-tenant.stat-card>
            </div>

            <x-tenant.list-toolbar>
                <x-slot:search>
                    <form method="GET" action="{{ route('tenant.bookings.index') }}" class="w-full">
                        <x-auth.icon-input
                            type="search"
                            name="search"
                            value="{{ $search }}"
                            placeholder="{{ __('Search by lead, property, or unit...') }}"
                        >
                            <x-slot:icon>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                            </x-slot:icon>
                        </x-auth.icon-input>
                    </form>
                </x-slot:search>

                <x-ui.button type="button" variant="default" @click="$dispatch('open-modal', 'create-booking')">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('Create Booking') }}
                </x-ui.button>
                <x-tenant.manageable-table.toolbar-button :data-table-key="$dataTableKey" />
            </x-tenant.list-toolbar>

            <x-auth-session-status class="mb-3 rounded-lg bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700" :status="session('status')" />

            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                    <h2 class="text-sm font-semibold text-black">{{ __('All Bookings') }}</h2>
                </div>

                <x-tenant.manageable-table.bulk-bar
                    :data-table-can-bulk-delete="$dataTableCanBulkDelete"
                    :data-table-bulk-delete-url="$dataTableBulkDeleteUrl"
                    :data-table-bulk-delete-param="$dataTableBulkDeleteParam"
                    :confirm-message="__('Are you sure you want to delete the selected bookings?')"
                />

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr class="align-middle">
                                <x-tenant.manageable-table.checkbox-header />
                                <th x-show="isColumnVisible('property')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Property') }}</th>
                                <th x-show="isColumnVisible('configuration')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Configuration') }}</th>
                                <th x-show="isColumnVisible('unit')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Unit') }}</th>
                                <th x-show="isColumnVisible('agreement_value')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Agreement Value') }}</th>
                                <th x-show="isColumnVisible('booking_date')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Booking Date') }}</th>
                                <th x-show="isColumnVisible('lead')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Lead') }}</th>
                                <th x-show="isColumnVisible('payout_amount')" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Payout Amount') }}</th>
                                <x-tenant.manageable-table.custom-column-headers />
                                <th x-show="isColumnVisible('actions')" class="whitespace-nowrap px-4 py-3 text-end align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($bookings as $booking)
                                @include('tenant.bookings.partials.booking-row', ['booking' => $booking])
                            @empty
                                <tr>
                                    <td colspan="20" class="px-4 py-12 text-center text-sm text-slate-500">
                                        {{ __('No bookings yet.') }}
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

        @include('tenant.bookings.partials.create-booking-modal', [
            'leads' => $leads,
            'properties' => $properties,
            'defaultLeadId' => $defaultLeadId,
        ])

        @foreach ($bookings as $booking)
            @include('tenant.bookings.partials.booking-details-modal', ['booking' => $booking])
            @if ($booking->canMarkAgreement())
                @include('tenant.bookings.partials.mark-agreement-modal', ['booking' => $booking])
            @endif
            @if ($booking->canCreateInvoice())
                @include('tenant.bookings.partials.create-invoice-modal', ['booking' => $booking])
            @endif
        @endforeach
    @endpush
</x-tenant-layout>

@props([
    'listing' => \App\Enums\LeadListingFilter::All,
])

<x-modal name="edit-columns" maxWidth="lg">
    @php
        $baseColumnOptions = [
            'name' => __('Lead Name'),
            'phone' => __('Phone'),
            'source' => __('Source'),
            'requirement' => __('Requirement'),
            'assigned_to' => __('Assigned To'),
            'status' => __('Lead Status'),
            'next_follow_up' => __('Next Follow-up'),
            'follow_ups_count' => __('Follow-ups'),
            'site_visits_count' => __('Site Visits'),
            'last_activity' => __('Last Activity'),
            'property_interest' => __('Property Interest'),
        ];

        $convertedColumnOptions = [
            'booking_date' => __('Booking Date'),
            'property_booked' => __('Property Booked'),
        ];

        $tailColumnOptions = [
            'created_at' => __('Created Date'),
            'actions' => __('Actions'),
        ];

        $columnOptions = $listing === \App\Enums\LeadListingFilter::Converted
            ? array_merge($baseColumnOptions, $convertedColumnOptions, $tailColumnOptions)
            : array_merge($baseColumnOptions, $tailColumnOptions);

        $actionOptions = [
            'call' => __('Call'),
            'whatsapp' => __('WhatsApp'),
            'follow_up' => __('Follow-up'),
            'site_visit' => __('Site Visit'),
            'create_booking' => __('Create Booking'),
        ];
    @endphp

    <div x-init="$store.leadTablePreferences.ensureLoaded()">
        <x-ui.modal.header
            :title="__('Edit Columns')"
            :description="__('Choose which columns and action buttons appear in the leads list')"
            modal-name="edit-columns"
        >
            <x-slot:icon>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.5-9h15m-15 6h15" />
                </svg>
            </x-slot:icon>
        </x-ui.modal.header>

        <x-ui.modal.body>
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-ui.modal.section :title="__('Columns')">
                        <div class="space-y-2">
                            @foreach ($columnOptions as $key => $label)
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-100 px-3 py-2 hover:bg-slate-50">
                                    <input
                                        type="checkbox"
                                        class="rounded border-slate-300 text-black focus:ring-navy"
                                        x-model="$store.leadTablePreferences.columns.{{ $key }}"
                                        @change="$store.leadTablePreferences.persistPreferences()"
                                        @if ($key === 'name') disabled @endif
                                    >
                                    <span class="text-sm font-medium text-black">{{ $label }}</span>
                                    @if ($key === 'name')
                                        <span class="ms-auto text-xs text-slate-400">{{ __('Required') }}</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </x-ui.modal.section>
                </div>

                <div>
                    <x-ui.modal.section :title="__('Action Buttons')">
                        <div class="space-y-2">
                            @foreach ($actionOptions as $key => $label)
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-100 px-3 py-2 hover:bg-slate-50">
                                    <input
                                        type="checkbox"
                                        class="rounded border-slate-300 text-black focus:ring-navy"
                                        x-model="$store.leadTablePreferences.actions.{{ $key }}"
                                        @change="$store.leadTablePreferences.persistPreferences()"
                                    >
                                    <span class="text-sm font-medium text-black">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        <button
                            type="button"
                            class="mt-4 text-sm font-semibold text-black hover:underline"
                            @click="$store.leadTablePreferences.resetPreferences()"
                        >
                            {{ __('Reset to defaults') }}
                        </button>
                    </x-ui.modal.section>
                </div>
            </div>
        </x-ui.modal.body>

        <x-ui.modal.footer>
            <x-ui.button type="button" variant="default" class="!rounded-xl !px-4 !py-2.5" @click="$dispatch('close-modal', 'edit-columns')">
                {{ __('Done') }}
            </x-ui.button>
        </x-ui.modal.footer>
    </div>
</x-modal>

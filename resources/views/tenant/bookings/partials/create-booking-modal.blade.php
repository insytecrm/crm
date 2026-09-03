@props([
    'leads',
    'properties',
    'defaultLeadId' => null,
    'modalName' => 'create-booking',
    'lockLead' => false,
])

@php
    $fieldId = 'booking_'.$modalName;
@endphp

<x-modal :name="$modalName" maxWidth="xl">
    <div
        x-data="{
            propertyId: @js(old('property_id', '')),
            configurationIndex: @js(old('configuration_index', '')),
            leadId: @js(old('lead_id', $defaultLeadId ?? '')),
            properties: @js($properties),
            get selectedProperty() {
                return this.properties.find((property) => String(property.id) === String(this.propertyId)) ?? null;
            },
            get configurations() {
                return this.selectedProperty?.configurations ?? [];
            },
            configurationLabel(configuration) {
                const parts = [configuration.name];

                if (configuration.carpet_area_sqft) {
                    parts.push(configuration.carpet_area_sqft + ' sqft');
                }

                if (configuration.price) {
                    parts.push('₹' + Number(configuration.price).toLocaleString('en-IN'));
                }

                return parts.join(' · ');
            },
            onPropertySelected(value) {
                this.propertyId = value;
                this.configurationIndex = '';
            },
        }"
    >
        <x-ui.modal.header
            :title="__('Create Booking')"
            :description="$lockLead && $leads->isNotEmpty()
                ? __('Select a property and configuration, then enter booking details.')
                : __('Select a lead, property, and configuration, then enter booking details.')"
            :modal-name="$modalName"
        >
            <x-slot:icon>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9" />
                </svg>
            </x-slot:icon>
        </x-ui.modal.header>

        <form method="POST" action="{{ route('tenant.bookings.store') }}">
            @csrf
            <x-ui.modal.body>
                <x-ui.modal.section :title="__('Lead & Property')">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-x-4 sm:gap-y-3">
                        <div class="sm:col-span-2">
                            <x-ui.modal.field-label for="booking_lead_id_{{ $modalName }}" :value="__('Related Lead')" />
                            @if ($leads->isEmpty())
                                <p class="text-sm text-amber-600">{{ __('Create a lead before booking a property.') }}</p>
                            @elseif ($lockLead)
                                <input type="hidden" name="lead_id" value="{{ $defaultLeadId }}">
                                <p class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-medium text-black">{{ $leads->first()->name }}</p>
                            @else
                                <x-ui.combobox
                                    id="booking_lead_id_{{ $modalName }}"
                                    name="lead_id"
                                    :options="collect($leads)->map(fn ($lead) => ['value' => (string) $lead->id, 'label' => $lead->name])->all()"
                                    :value="old('lead_id', $defaultLeadId ?? '')"
                                    :placeholder="__('Select a lead')"
                                    required
                                    x-on:selected="leadId = $event.detail"
                                />
                                @error('lead_id')
                                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                        <div>
                            <x-ui.modal.field-label for="{{ $fieldId }}_property" :value="__('Property')" required />
                            <x-ui.combobox
                                id="{{ $fieldId }}_property"
                                name="property_id"
                                :options="collect($properties)->map(fn ($property) => ['value' => (string) $property['id'], 'label' => $property['label']])->all()"
                                :value="old('property_id', '')"
                                :placeholder="__('Select a property')"
                                required
                                @selected="onPropertySelected($event.detail)"
                            />
                            @error('property_id')
                                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div x-show="propertyId !== ''" x-cloak>
                            <x-ui.modal.field-label for="{{ $fieldId }}_configuration" :value="__('Configuration')" required />
                            <select
                                id="{{ $fieldId }}_configuration"
                                name="configuration_index"
                                x-model="configurationIndex"
                                required
                                class="mt-0 block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm focus:border-navy focus:ring-navy"
                            >
                                <option value="">{{ __('Select configuration') }}</option>
                                <template x-for="(configuration, index) in configurations" :key="index">
                                    <option :value="index" x-text="configurationLabel(configuration)"></option>
                                </template>
                            </select>
                            @error('configuration_index')
                                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </x-ui.modal.section>

                <x-ui.modal.section :title="__('Booking Details')">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-x-4 sm:gap-y-3">
                        <div>
                            <x-ui.modal.field-label for="{{ $fieldId }}_unit" :value="__('Unit Number')" required />
                            <x-auth.icon-input
                                id="{{ $fieldId }}_unit"
                                name="unit_number"
                                :value="old('unit_number')"
                                required
                                placeholder="{{ __('e.g. 1204') }}"
                            />
                            @error('unit_number')
                                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <x-ui.modal.field-label for="{{ $fieldId }}_agreement" :value="__('Agreement Value (₹)')" required />
                            <x-auth.icon-input
                                id="{{ $fieldId }}_agreement"
                                name="agreement_value"
                                type="number"
                                min="1"
                                :value="old('agreement_value')"
                                required
                                placeholder="{{ __('Enter amount') }}"
                            />
                            @error('agreement_value')
                                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <x-ui.modal.field-label for="{{ $fieldId }}_date" :value="__('Booking Date')" required />
                            <x-ui.datetime-picker
                                id="{{ $fieldId }}_date"
                                name="booking_date"
                                mode="date"
                                :value="old('booking_date', now()->toDateString())"
                                required
                            />
                            @error('booking_date')
                                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </x-ui.modal.section>
            </x-ui.modal.body>

            <x-ui.modal.footer>
                <x-ui.modal.cancel-button :modal-name="$modalName" />
                <x-ui.modal.submit-button :disabled="$leads->isEmpty()">{{ __('Create Booking') }}</x-ui.modal.submit-button>
            </x-ui.modal.footer>
        </form>
    </div>
</x-modal>

@props([
    'leads',
    'properties',
    'defaultLeadId' => null,
    'modalName' => 'create-booking',
    'lockLead' => false,
])

@php
    $fieldId = 'booking_'.$modalName;
    $configSelectId = $fieldId.'_configuration';

    $configsByProperty = collect($properties)
        ->mapWithKeys(function (array $property) {
            $options = collect($property['configurations'] ?? [])
                ->values()
                ->map(function (array $configuration, int $index) {
                    $parts = [$configuration['name'] ?? ''];

                    if (! empty($configuration['carpet_area_sqft'])) {
                        $parts[] = $configuration['carpet_area_sqft'].' sqft';
                    }

                    if (! empty($configuration['price'])) {
                        $parts[] = '₹'.number_format((int) $configuration['price']);
                    }

                    return [
                        'value' => (string) $index,
                        'label' => implode(' · ', array_filter($parts)),
                    ];
                })
                ->all();

            return [(string) $property['id'] => $options];
        })
        ->all();
@endphp

<x-modal :name="$modalName" maxWidth="xl">
    <div
        x-data="{
            propertyId: @js((string) old('property_id', '')),
            configurationIndex: @js(old('configuration_index', '') === null || old('configuration_index', '') === '' ? '' : (string) old('configuration_index')),
            configsByProperty: @js($configsByProperty),
            fillConfigurationOptions() {
                const select = this.$refs.configSelect;

                if (! select) {
                    return;
                }

                const previous = String(this.configurationIndex ?? '');
                const matching = this.configsByProperty[String(this.propertyId)] ?? [];

                while (select.options.length > 1) {
                    select.remove(1);
                }

                matching.forEach((option) => {
                    select.add(new Option(option.label, option.value));
                });

                const values = matching.map((option) => String(option.value));
                const nextValue = values.includes(previous) ? previous : '';

                if (this.configurationIndex !== nextValue) {
                    this.configurationIndex = nextValue;
                }

                select.value = nextValue;
            },
            init() {
                this.$watch('propertyId', () => {
                    this.configurationIndex = '';
                    this.$nextTick(() => this.fillConfigurationOptions());
                });

                this.$nextTick(() => this.fillConfigurationOptions());
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
            <input type="hidden" name="_open_modal" value="{{ $modalName }}">
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
                                />
                                @error('lead_id')
                                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                        <div>
                            <x-ui.modal.field-label for="{{ $fieldId }}_property" :value="__('Property')" required />
                            <select
                                id="{{ $fieldId }}_property"
                                name="property_id"
                                x-model="propertyId"
                                required
                                class="mt-0 block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm focus:border-navy focus:ring-navy"
                            >
                                <option value="">{{ __('Select a property') }}</option>
                                @foreach ($properties as $property)
                                    <option value="{{ $property['id'] }}">
                                        {{ $property['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('property_id')
                                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <x-ui.modal.field-label for="{{ $configSelectId }}" :value="__('Configuration')" required />
                            <select
                                id="{{ $configSelectId }}"
                                name="configuration_index"
                                x-ref="configSelect"
                                x-model="configurationIndex"
                                :disabled="propertyId === ''"
                                :required="propertyId !== ''"
                                class="mt-0 block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm focus:border-navy focus:ring-navy disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400"
                            >
                                <option value="">{{ __('Select configuration') }}</option>
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

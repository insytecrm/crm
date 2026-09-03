@php
    $ph = config('property-form.placeholders');
    $property = $property ?? null;
    $idPrefix = $idPrefix ?? '';
    $fieldId = fn (string $name): string => $idPrefix.$name;
    $fieldValue = fn (string $name, mixed $default = null): mixed => old($name, $property?->{$name} ?? $default);
@endphp

<x-tenant.form-section-card :title="__('Basic Information')" compact>
    <x-slot:icon>
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
        </svg>
    </x-slot:icon>

    <div class="property-form-rows">
        <div class="property-form-row">
            <div class="property-form-field">
                <x-input-label :for="$fieldId('developer_name')" :value="__('Developer Name')" />
                <x-auth.icon-input :id="$fieldId('developer_name')" name="developer_name" :placeholder="$ph['developer_name']" :value="$fieldValue('developer_name')" />
                <x-input-error class="mt-1" :messages="$errors->get('developer_name')" />
            </div>

            <div class="property-form-field">
                <x-input-label :for="$fieldId('project_name')" :value="__('Project Name')" />
                <x-auth.icon-input :id="$fieldId('project_name')" name="project_name" required :placeholder="$ph['project_name']" :value="$fieldValue('project_name')" />
                <x-input-error class="mt-1" :messages="$errors->get('project_name')" />
            </div>

            <div class="property-form-field">
                <x-input-label :for="$fieldId('project_location')" :value="__('Project Location')" />
                <x-auth.icon-input :id="$fieldId('project_location')" name="project_location" :placeholder="$ph['project_location']" :value="$fieldValue('project_location')" />
                <x-input-error class="mt-1" :messages="$errors->get('project_location')" />
            </div>
        </div>

        <div class="property-form-row">
            <div class="property-form-field">
                <x-input-label :for="$fieldId('rera_number')" :value="__('RERA Number')" />
                <x-auth.icon-input :id="$fieldId('rera_number')" name="rera_number" :placeholder="$ph['rera_number']" :value="$fieldValue('rera_number')" />
                <x-input-error class="mt-1" :messages="$errors->get('rera_number')" />
            </div>

            <div class="property-form-field">
                <x-input-label :for="$fieldId('property_type')" :value="__('Property Type')" />
                <x-ui.combobox
                    :id="$fieldId('property_type')"
                    name="property_type"
                    :options="collect($propertyTypes)->map(fn ($type) => ['value' => $type->value, 'label' => $type->label()])->all()"
                    :value="old('property_type', $property?->property_type?->value)"
                    :searchable="false"
                    :placeholder="$ph['property_type']"
                />
                <x-input-error class="mt-1" :messages="$errors->get('property_type')" />
            </div>

            <div class="property-form-field">
                <x-input-label :for="$fieldId('project_status')" :value="__('Project Status')" />
                <x-ui.combobox
                    :id="$fieldId('project_status')"
                    name="project_status"
                    :options="collect($projectStatuses)->map(fn ($status) => ['value' => $status->value, 'label' => $status->label()])->all()"
                    :value="old('project_status', $property?->project_status?->value)"
                    :searchable="false"
                    :placeholder="$ph['project_status']"
                />
                <x-input-error class="mt-1" :messages="$errors->get('project_status')" />
            </div>
        </div>

        <div class="property-form-row">
            <div class="property-form-field">
                <x-input-label :for="$fieldId('possession_date')" :value="__('Possession Date')" />
                <x-auth.icon-input
                    :id="$fieldId('possession_date')"
                    name="possession_date"
                    :placeholder="$ph['possession_date']"
                    inputmode="numeric"
                    maxlength="7"
                    :value="$fieldValue('possession_date')"
                />
                <x-input-error class="mt-1" :messages="$errors->get('possession_date')" />
            </div>
        </div>
    </div>
</x-tenant.form-section-card>

<x-tenant.form-section-card :title="__('Project Scale')" compact>
    <x-slot:icon>
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
        </svg>
    </x-slot:icon>

    <div class="property-form-rows">
        <div class="property-form-row">
            <div class="property-form-field">
                <x-input-label :for="$fieldId('total_land_parcel_acres')" :value="__('Total Land Parcel (Acres)')" />
                <x-auth.icon-input :id="$fieldId('total_land_parcel_acres')" type="number" step="0.01" name="total_land_parcel_acres" :placeholder="$ph['total_land_parcel_acres']" :value="$fieldValue('total_land_parcel_acres')" />
                <x-input-error class="mt-1" :messages="$errors->get('total_land_parcel_acres')" />
            </div>

            <div class="property-form-field">
                <x-input-label :for="$fieldId('total_towers')" :value="__('Total Towers')" />
                <x-auth.icon-input :id="$fieldId('total_towers')" type="number" name="total_towers" :placeholder="$ph['total_towers']" :value="$fieldValue('total_towers')" />
                <x-input-error class="mt-1" :messages="$errors->get('total_towers')" />
            </div>

            <div class="property-form-field">
                <x-input-label :for="$fieldId('total_floors')" :value="__('Total Floors')" />
                <x-auth.icon-input :id="$fieldId('total_floors')" name="total_floors" :placeholder="$ph['total_floors']" :value="$fieldValue('total_floors')" />
                <x-input-error class="mt-1" :messages="$errors->get('total_floors')" />
            </div>
        </div>

        <div class="property-form-row">
            <div class="property-form-field">
                <x-input-label :for="$fieldId('carpet_area_from_sqft')" :value="__('Carpet Area From (Sq.ft)')" />
                <x-auth.icon-input :id="$fieldId('carpet_area_from_sqft')" type="number" name="carpet_area_from_sqft" :placeholder="$ph['carpet_area_from_sqft']" :value="$fieldValue('carpet_area_from_sqft')" />
                <x-input-error class="mt-1" :messages="$errors->get('carpet_area_from_sqft')" />
            </div>

            <div class="property-form-field">
                <x-input-label :for="$fieldId('carpet_area_to_sqft')" :value="__('Carpet Area To (Sq.ft)')" />
                <x-auth.icon-input :id="$fieldId('carpet_area_to_sqft')" type="number" name="carpet_area_to_sqft" :placeholder="$ph['carpet_area_to_sqft']" :value="$fieldValue('carpet_area_to_sqft')" />
                <x-input-error class="mt-1" :messages="$errors->get('carpet_area_to_sqft')" />
            </div>
        </div>

        <div class="property-form-row">
            <div class="property-form-field">
                <x-input-label :for="$fieldId('price_from')" :value="__('Price Range From (₹)')" />
                <x-auth.icon-input :id="$fieldId('price_from')" type="number" name="price_from" :placeholder="$ph['price_from']" :value="$fieldValue('price_from')" />
                <x-input-error class="mt-1" :messages="$errors->get('price_from')" />
            </div>

            <div class="property-form-field">
                <x-input-label :for="$fieldId('price_to')" :value="__('Price Range To (₹)')" />
                <x-auth.icon-input :id="$fieldId('price_to')" type="number" name="price_to" :placeholder="$ph['price_to']" :value="$fieldValue('price_to')" />
                <x-input-error class="mt-1" :messages="$errors->get('price_to')" />
            </div>
        </div>
    </div>
</x-tenant.form-section-card>

<x-tenant.form-section-card :title="__('Tagging & Payout')" compact>
    <x-slot:icon>
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
        </svg>
    </x-slot:icon>

    <div class="property-form-rows">
        <div class="property-form-row">
            <div class="property-form-field">
                <x-input-label :for="$fieldId('tagging_period_days')" :value="__('Tagging Period (Days)')" />
                <x-auth.icon-input :id="$fieldId('tagging_period_days')" type="number" name="tagging_period_days" :placeholder="$ph['tagging_period_days']" :value="$fieldValue('tagging_period_days')" />
                <x-input-error class="mt-1" :messages="$errors->get('tagging_period_days')" />
            </div>

            <div class="property-form-field">
                <x-input-label :for="$fieldId('payout_percent')" :value="__('Payout (%)')" />
                <x-auth.icon-input :id="$fieldId('payout_percent')" type="number" step="0.01" name="payout_percent" :placeholder="$ph['payout_percent']" :value="$fieldValue('payout_percent')" />
                <x-input-error class="mt-1" :messages="$errors->get('payout_percent')" />
            </div>

            <div class="property-form-field">
                <x-input-label :for="$fieldId('sourcing_manager_name')" :value="__('Sourcing Manager Name')" />
                <x-auth.icon-input :id="$fieldId('sourcing_manager_name')" name="sourcing_manager_name" :placeholder="$ph['sourcing_manager_name']" :value="$fieldValue('sourcing_manager_name')" />
                <x-input-error class="mt-1" :messages="$errors->get('sourcing_manager_name')" />
            </div>
        </div>

        <div class="property-form-row">
            <div class="property-form-field">
                <x-input-label :for="$fieldId('sourcing_manager_contact')" :value="__('Sourcing Manager Contact')" />
                <x-auth.icon-input :id="$fieldId('sourcing_manager_contact')" name="sourcing_manager_contact" type="tel" inputmode="tel" :placeholder="$ph['sourcing_manager_contact']" :value="$fieldValue('sourcing_manager_contact')" />
                <x-input-error class="mt-1" :messages="$errors->get('sourcing_manager_contact')" />
            </div>
        </div>
    </div>
</x-tenant.form-section-card>

<x-tenant.form-section-card :title="__('Amenities')" compact>
    <x-slot:icon>
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
        </svg>
    </x-slot:icon>

    <div class="property-form-rows">
        <div class="property-form-field">
            <x-ui.tag-input
                name="amenities"
                :id="$fieldId('amenities')"
                :value="old('amenities', $property?->amenities ?? [])"
                :placeholder="$ph['amenity_input']"
                :hint="__('Type an amenity and press Enter or click Add. Add as many as needed.')"
            />
            @foreach ($errors->keys() as $errorKey)
                @if (str_starts_with($errorKey, 'amenities'))
                    <x-input-error class="mt-1" :messages="$errors->get($errorKey)" />
                @endif
            @endforeach
        </div>
    </div>
</x-tenant.form-section-card>

<x-tenant.form-section-card :title="__('Configurations (Unit Variants)')" compact>
    <x-slot:icon>
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
        </svg>
    </x-slot:icon>

    <div class="property-form-rows">
        <div class="property-form-field">
            <x-ui.configuration-repeater
                name="configurations"
                :value="old('configurations', $property?->configurations ?? [])"
                :placeholders="[
                    'name' => $ph['configuration_name'],
                    'carpet_area_sqft' => $ph['configuration_carpet_area_sqft'],
                    'price' => $ph['configuration_price'],
                    'unit_count' => $ph['configuration_unit_count'],
                ]"
                :hint="__('e.g. 2 BHK, 3 BHK + Study, 4 BHK Duplex — each with its own carpet area, price, and unit count.')"
            />
            @foreach ($errors->keys() as $errorKey)
                @if (str_starts_with($errorKey, 'configurations'))
                    <x-input-error class="mt-1" :messages="$errors->get($errorKey)" />
                @endif
            @endforeach
        </div>
    </div>
</x-tenant.form-section-card>

<x-tenant.form-section-card :title="__('Attachments')" compact>
    <x-slot:icon>
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739V9.375a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 5.625v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
        </svg>
    </x-slot:icon>

    @if ($property && (($property->layout_files ?? []) !== [] || ($property->brochure_files ?? []) !== []))
        <div class="mb-3 space-y-3 rounded-lg border border-slate-100 bg-slate-50 p-3">
            @if (($property->layout_files ?? []) !== [])
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Existing Layouts') }}</p>
                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                        @foreach ($property->layout_files as $file)
                            <span class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">
                                {{ $file['name'] ?? __('File') }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
            @if (($property->brochure_files ?? []) !== [])
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Existing Brochures') }}</p>
                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                        @foreach ($property->brochure_files as $file)
                            <span class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">
                                {{ $file['name'] ?? __('File') }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="property-form-attachments">
        <div class="property-form-field">
            <x-input-label :for="$fieldId('layout_files')" :value="__('Layouts')" />
            <input
                type="file"
                :id="$fieldId('layout_files')"
                name="layout_files[]"
                multiple
                accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"
                class="property-form-file"
            >
            <p class="property-form-file-hint">{{ __('Choose files') }}</p>
            @foreach ($errors->keys() as $errorKey)
                @if (str_starts_with($errorKey, 'layout_files'))
                    <x-input-error class="mt-1" :messages="$errors->get($errorKey)" />
                @endif
            @endforeach
        </div>

        <div class="property-form-field">
            <x-input-label :for="$fieldId('brochure_files')" :value="__('Brochure Files')" />
            <input
                type="file"
                :id="$fieldId('brochure_files')"
                name="brochure_files[]"
                multiple
                accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"
                class="property-form-file"
            >
            <p class="property-form-file-hint">{{ __('Choose files') }}</p>
            @foreach ($errors->keys() as $errorKey)
                @if (str_starts_with($errorKey, 'brochure_files'))
                    <x-input-error class="mt-1" :messages="$errors->get($errorKey)" />
                @endif
            @endforeach
        </div>
    </div>
</x-tenant.form-section-card>

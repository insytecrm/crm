@props([
    'lead',
    'properties' => null,
])

@php
    $properties = $properties ?? \App\Models\Property::bookingFormOptions();
@endphp

<x-modal :name="'site-visit-'.$lead->id" maxWidth="lg">
    <x-ui.modal.header
        :title="__('Schedule Site Visit')"
        :description="__('Choose a property, set the visit date, and add optional notes')"
        :modal-name="'site-visit-'.$lead->id"
    >
        <x-slot:icon>
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>
        </x-slot:icon>
    </x-ui.modal.header>

    <form method="POST" action="{{ route('tenant.leads.site-visit.store', $lead) }}">
        @csrf
        <x-ui.modal.body>
            <x-ui.modal.section :title="__('Site Visit Details')">
                <div class="space-y-4">
                    <div>
                        <x-ui.modal.field-label for="site_visit_property_{{ $lead->id }}" :value="__('Property')" required />
                        @if ($properties === [])
                            <p class="text-sm text-amber-600">{{ __('Add a property before scheduling a site visit.') }}</p>
                        @else
                            <x-ui.combobox
                                id="site_visit_property_{{ $lead->id }}"
                                name="property_id"
                                :options="collect($properties)->map(fn ($property) => ['value' => (string) $property['id'], 'label' => $property['label']])->all()"
                                :value="old('property_id', '')"
                                :placeholder="__('Select a property')"
                                required
                            />
                            @error('property_id')
                                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                    <div>
                        <x-ui.modal.field-label for="site_visit_type_{{ $lead->id }}" :value="__('Visit Type')" required />
                        <x-ui.form-select
                            id="site_visit_type_{{ $lead->id }}"
                            name="visit_type"
                            :value="old('visit_type', \App\Enums\SiteVisitType::FreshVisit->value)"
                            :options="collect(\App\Enums\SiteVisitType::options())->map(fn ($visitType) => ['value' => $visitType->value, 'label' => $visitType->label()])->all()"
                            :placeholder="__('Select visit type')"
                            required
                        />
                        @error('visit_type')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-ui.modal.field-label for="site_visit_at_{{ $lead->id }}" :value="__('Date & Time')" required />
                        <x-ui.datetime-picker
                            id="site_visit_at_{{ $lead->id }}"
                            name="upcoming_site_visit_at"
                            required
                        />
                    </div>
                    <div>
                        <x-ui.modal.field-label for="site_visit_notes_{{ $lead->id }}" :value="__('Notes')" />
                        <textarea id="site_visit_notes_{{ $lead->id }}" name="notes" rows="3" class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" placeholder="{{ __('Add notes (optional)') }}"></textarea>
                    </div>
                    @include('tenant.partials.reminder-fields', [
                        'idPrefix' => 'site_visit_reminder_'.$lead->id,
                    ])
                </div>
            </x-ui.modal.section>
        </x-ui.modal.body>

        <x-ui.modal.footer>
            <x-ui.modal.cancel-button :modal-name="'site-visit-'.$lead->id" />
            <x-ui.modal.submit-button :disabled="$properties === []">{{ __('Schedule') }}</x-ui.modal.submit-button>
        </x-ui.modal.footer>
    </form>
</x-modal>

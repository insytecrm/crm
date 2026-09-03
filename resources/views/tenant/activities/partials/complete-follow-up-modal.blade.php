@props([
    'modalName',
    'action',
    'activityLabel',
    'eventId',
    'properties' => null,
])

@php
    $properties = $properties ?? \App\Models\Property::bookingFormOptions();
@endphp

<x-modal :name="$modalName" maxWidth="lg">
    <x-ui.modal.header
        :title="__('Complete Follow-up')"
        :description="__('Record how you contacted the lead and what to do next')"
        :modal-name="$modalName"
    >
        <x-slot:icon>
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </x-slot:icon>
    </x-ui.modal.header>

    <form
        method="POST"
        action="{{ $action }}"
        x-data="{
            nextStep: '{{ \App\Enums\ScheduledActivityNextStep::None->value }}',
            showFollowUpFields() {
                return this.nextStep === '{{ \App\Enums\ScheduledActivityNextStep::ScheduleFollowUp->value }}';
            },
            showSiteVisitFields() {
                return this.nextStep === '{{ \App\Enums\ScheduledActivityNextStep::ScheduleSiteVisit->value }}';
            },
            showTaskFields() {
                return this.nextStep === '{{ \App\Enums\ScheduledActivityNextStep::CreateTask->value }}';
            },
            showScheduledAt() {
                return this.showFollowUpFields() || this.showSiteVisitFields();
            },
        }"
    >
        @csrf
        <x-ui.modal.body>
            <x-ui.modal.section :title="$activityLabel">
                <div class="space-y-4">
                    <div>
                        <x-ui.modal.field-label for="contact_method_{{ $eventId }}" :value="__('Contact method')" required />
                        <x-ui.form-select
                            id="contact_method_{{ $eventId }}"
                            name="contact_method"
                            :options="collect(\App\Enums\ScheduledActivityContactMethod::options())->map(fn ($method) => ['value' => $method->value, 'label' => $method->label()])->all()"
                            :placeholder="__('Select contact method')"
                            required
                        />
                    </div>
                    <div>
                        <x-ui.modal.field-label for="outcome_{{ $eventId }}" :value="__('Outcome')" required />
                        <x-ui.form-select
                            id="outcome_{{ $eventId }}"
                            name="outcome"
                            :options="collect(\App\Enums\ScheduledActivityOutcome::options())->map(fn ($outcome) => ['value' => $outcome->value, 'label' => $outcome->label()])->all()"
                            :placeholder="__('Select outcome')"
                            required
                        />
                    </div>
                    <div>
                        <x-ui.modal.field-label for="notes_complete_{{ $eventId }}" :value="__('Notes')" />
                        <textarea
                            id="notes_complete_{{ $eventId }}"
                            name="notes"
                            rows="3"
                            class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy"
                            placeholder="{{ __('Add notes (optional)') }}"
                        ></textarea>
                    </div>
                </div>
            </x-ui.modal.section>

            <x-ui.modal.section :title="__('What next?')">
                <div class="space-y-4">
                    <div>
                        <x-ui.modal.field-label for="next_step_{{ $eventId }}" :value="__('Next step')" required />
                        <x-ui.form-select
                            id="next_step_{{ $eventId }}"
                            name="next_step"
                            :value="\App\Enums\ScheduledActivityNextStep::None->value"
                            :options="collect(\App\Enums\ScheduledActivityNextStep::options())->map(fn ($step) => ['value' => $step->value, 'label' => $step->label()])->all()"
                            :placeholder="__('Select next step')"
                            required
                            x-model="nextStep"
                        />
                    </div>

                    <div x-show="showSiteVisitFields()" x-cloak>
                        <x-ui.modal.field-label for="next_property_id_{{ $eventId }}" :value="__('Property')" />
                        @if ($properties === [])
                            <p class="text-sm text-amber-600">{{ __('Add a property before scheduling a site visit.') }}</p>
                        @else
                            <x-ui.combobox
                                id="next_property_id_{{ $eventId }}"
                                name="next_property_id"
                                :options="collect($properties)->map(fn ($property) => ['value' => (string) $property['id'], 'label' => $property['label']])->all()"
                                :value="old('next_property_id', '')"
                                :placeholder="__('Select a property')"
                            />
                        @endif
                    </div>

                    <div x-show="showSiteVisitFields()" x-cloak>
                        <x-ui.modal.field-label for="next_visit_type_{{ $eventId }}" :value="__('Visit Type')" />
                        <x-ui.form-select
                            id="next_visit_type_{{ $eventId }}"
                            name="next_visit_type"
                            :value="old('next_visit_type', \App\Enums\SiteVisitType::FreshVisit->value)"
                            :options="collect(\App\Enums\SiteVisitType::options())->map(fn ($visitType) => ['value' => $visitType->value, 'label' => $visitType->label()])->all()"
                            :placeholder="__('Select visit type')"
                        />
                    </div>

                    <div x-show="showScheduledAt()" x-cloak>
                        <x-ui.modal.field-label for="next_scheduled_at_{{ $eventId }}" :value="__('Date & Time')" />
                        <x-ui.datetime-picker
                            id="next_scheduled_at_{{ $eventId }}"
                            name="next_scheduled_at"
                        />
                    </div>

                    <div x-show="showFollowUpFields()" x-cloak>
                        <x-ui.modal.field-label for="next_priority_{{ $eventId }}" :value="__('Priority')" />
                        @include('tenant.activities.partials.priority-select', [
                            'id' => 'next_priority_'.$eventId,
                            'name' => 'next_priority',
                        ])
                    </div>

                    <div x-show="showTaskFields()" x-cloak>
                        <x-ui.modal.field-label for="task_title_{{ $eventId }}" :value="__('Task title')" />
                        <input
                            id="task_title_{{ $eventId }}"
                            type="text"
                            name="task_title"
                            class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm focus:border-navy focus:ring-navy"
                            placeholder="{{ __('Follow up with documents') }}"
                        >
                    </div>

                    <div x-show="showTaskFields()" x-cloak>
                        <x-ui.modal.field-label for="task_due_at_{{ $eventId }}" :value="__('Task due date')" />
                        <x-ui.datetime-picker
                            id="task_due_at_{{ $eventId }}"
                            name="task_due_at"
                            mode="date"
                        />
                    </div>

                    <div x-show="nextStep !== '{{ \App\Enums\ScheduledActivityNextStep::None->value }}'" x-cloak>
                        <x-ui.modal.field-label for="next_notes_{{ $eventId }}" :value="__('Notes for next step')" />
                        <textarea
                            id="next_notes_{{ $eventId }}"
                            name="next_notes"
                            rows="2"
                            class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy"
                            placeholder="{{ __('Add notes (optional)') }}"
                        ></textarea>
                    </div>
                </div>
            </x-ui.modal.section>
        </x-ui.modal.body>

        <x-ui.modal.footer>
            <x-ui.modal.cancel-button :modal-name="$modalName" />
            <x-ui.modal.submit-button>{{ __('Complete follow-up') }}</x-ui.modal.submit-button>
        </x-ui.modal.footer>
    </form>
</x-modal>

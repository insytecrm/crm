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
        :title="__('Complete Site Visit')"
        :description="__('Record attendance, outcome, and what to do next')"
        :modal-name="$modalName"
    >
        <x-slot:icon>
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>
        </x-slot:icon>
    </x-ui.modal.header>

    <form
        method="POST"
        action="{{ $action }}"
        x-data="{
            attended: '1',
            nextStep: '{{ \App\Enums\SiteVisitNextStep::None->value }}',
            showOutcome() {
                return this.attended === '1';
            },
            showFollowUpFields() {
                return this.nextStep === '{{ \App\Enums\SiteVisitNextStep::ScheduleFollowUp->value }}';
            },
            showSiteVisitFields() {
                return this.nextStep === '{{ \App\Enums\SiteVisitNextStep::ScheduleSiteVisit->value }}';
            },
            showTaskFields() {
                return this.nextStep === '{{ \App\Enums\SiteVisitNextStep::CreateTask->value }}';
            },
            showScheduledAt() {
                return this.showFollowUpFields() || this.showSiteVisitFields();
            },
            showNextNotes() {
                return this.nextStep !== '{{ \App\Enums\SiteVisitNextStep::None->value }}'
                    && this.nextStep !== '{{ \App\Enums\SiteVisitNextStep::CreateBooking->value }}';
            },
        }"
    >
        @csrf
        <x-ui.modal.body>
            <x-ui.modal.section :title="$activityLabel">
                <div class="space-y-4">
                    <div>
                        <x-ui.modal.field-label for="attended_{{ $eventId }}" :value="__('Attended')" required />
                        @php
                            $attendedOptions = [
                                ['value' => '1', 'label' => __('Yes')],
                                ['value' => '0', 'label' => __('No')],
                            ];
                        @endphp
                        <x-ui.form-select
                            id="attended_{{ $eventId }}"
                            name="attended"
                            value="1"
                            :options="$attendedOptions"
                            :placeholder="__('Select')"
                            required
                            x-model="attended"
                        />
                    </div>
                    <div x-show="showOutcome()" x-cloak>
                        <x-ui.modal.field-label for="outcome_{{ $eventId }}" :value="__('Outcome')" required />
                        <x-ui.form-select
                            id="outcome_{{ $eventId }}"
                            name="outcome"
                            :options="collect(\App\Enums\SiteVisitOutcome::options())->map(fn ($outcome) => ['value' => $outcome->value, 'label' => $outcome->label()])->all()"
                            :placeholder="__('Select outcome')"
                            required
                            x-bind:data-disabled="! showOutcome()"
                        />
                    </div>
                    <div>
                        <x-ui.modal.field-label for="notes_complete_sv_{{ $eventId }}" :value="__('Notes')" />
                        <textarea
                            id="notes_complete_sv_{{ $eventId }}"
                            name="notes"
                            rows="3"
                            class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy"
                            placeholder="{{ __('Add notes (optional)') }}"
                        ></textarea>
                    </div>
                </div>
            </x-ui.modal.section>

            <x-ui.modal.section :title="__('Next action')">
                <div class="space-y-4">
                    <div>
                        <x-ui.modal.field-label for="next_step_{{ $eventId }}" :value="__('Next action')" required />
                        <x-ui.form-select
                            id="next_step_{{ $eventId }}"
                            name="next_step"
                            :value="\App\Enums\SiteVisitNextStep::None->value"
                            :options="collect(\App\Enums\SiteVisitNextStep::options())->map(fn ($step) => ['value' => $step->value, 'label' => $step->label()])->all()"
                            :placeholder="__('Select next action')"
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
                            :value="old('next_visit_type', \App\Enums\SiteVisitType::Revisit->value)"
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
                            placeholder="{{ __('Send quotation') }}"
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

                    <div x-show="showNextNotes()" x-cloak>
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
            <x-ui.modal.submit-button>{{ __('Complete site visit') }}</x-ui.modal.submit-button>
        </x-ui.modal.footer>
    </form>
</x-modal>

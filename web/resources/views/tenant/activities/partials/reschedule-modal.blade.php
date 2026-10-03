@props([
    'modalName',
    'action',
    'isFollowUp',
    'eventId',
    'scheduledAt' => null,
    'notes' => null,
    'priority' => null,
])

@php
    $title = $isFollowUp ? __('Reschedule Follow-up') : __('Reschedule Site Visit');
    $description = $isFollowUp
        ? __('Pick a new follow-up date and optional notes for this lead')
        : __('Pick a new site visit date and optional notes for this lead');
    $sectionTitle = $isFollowUp ? __('Follow-up Details') : __('Site Visit Details');
    $inputValue = $scheduledAt?->format('Y-m-d\TH:i') ?? '';
@endphp

<x-modal :name="$modalName" maxWidth="lg">
    <x-ui.modal.header
        :title="$title"
        :description="$description"
        :modal-name="$modalName"
    >
        <x-slot:icon>
            @if ($isFollowUp)
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 4.5 19 2" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 4.5 5 2" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l2.25 2.25" />
                </svg>
            @else
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
            @endif
        </x-slot:icon>
    </x-ui.modal.header>

    <form method="POST" action="{{ $action }}">
        @csrf
        @method('PATCH')
        <x-ui.modal.body>
            <x-ui.modal.section :title="$sectionTitle">
                <div class="space-y-4">
                    <div>
                        <x-ui.modal.field-label for="scheduled_at_reschedule_{{ $eventId }}" :value="__('Date & Time')" required />
                        <x-ui.datetime-picker
                            id="scheduled_at_reschedule_{{ $eventId }}"
                            name="scheduled_at"
                            :value="$inputValue"
                            required
                        />
                    </div>
                    @if ($isFollowUp)
                        <div>
                            <x-ui.modal.field-label for="priority_reschedule_{{ $eventId }}" :value="__('Priority')" required />
                            @include('tenant.activities.partials.priority-select', [
                                'id' => 'priority_reschedule_'.$eventId,
                                'name' => 'priority',
                                'value' => $priority?->value,
                                'required' => true,
                            ])
                        </div>
                    @endif
                    <div>
                        <x-ui.modal.field-label for="notes_reschedule_{{ $eventId }}" :value="__('Notes')" />
                        <textarea
                            id="notes_reschedule_{{ $eventId }}"
                            name="notes"
                            rows="3"
                            class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy"
                            placeholder="{{ __('Add notes (optional)') }}"
                        >{{ $notes }}</textarea>
                    </div>
                </div>
            </x-ui.modal.section>
        </x-ui.modal.body>

        <x-ui.modal.footer>
            <x-ui.modal.cancel-button :modal-name="$modalName" />
            <x-ui.modal.submit-button>{{ __('Reschedule') }}</x-ui.modal.submit-button>
        </x-ui.modal.footer>
    </form>
</x-modal>

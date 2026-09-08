<x-modal :name="'follow-up-'.$lead->id" maxWidth="lg">
    <x-ui.modal.header
        :title="__('Schedule Follow-up')"
        :description="__('Set a follow-up date and optional notes for this lead')"
        :modal-name="'follow-up-'.$lead->id"
    >
        <x-slot:icon>
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 4.5 19 2" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 4.5 5 2" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l2.25 2.25" />
            </svg>
        </x-slot:icon>
    </x-ui.modal.header>

    <form method="POST" action="{{ route('tenant.leads.follow-up.store', $lead) }}">
        @csrf
        <x-ui.modal.body>
            <x-ui.modal.section :title="__('Follow-up Details')">
                <div class="space-y-4">
                    <div>
                        <x-ui.modal.field-label for="next_follow_up_at_{{ $lead->id }}" :value="__('Date & Time')" required />
                        <x-ui.datetime-picker
                            id="next_follow_up_at_{{ $lead->id }}"
                            name="next_follow_up_at"
                            required
                        />
                    </div>
                    <div>
                        <x-ui.modal.field-label for="priority_{{ $lead->id }}" :value="__('Priority')" required />
                        @include('tenant.activities.partials.priority-select', [
                            'id' => 'priority_'.$lead->id,
                            'name' => 'priority',
                            'required' => true,
                        ])
                    </div>
                    <div>
                        <x-ui.modal.field-label for="notes_{{ $lead->id }}" :value="__('Notes')" />
                        <textarea id="notes_{{ $lead->id }}" name="notes" rows="3" class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" placeholder="{{ __('Add notes (optional)') }}"></textarea>
                    </div>
                </div>
            </x-ui.modal.section>
        </x-ui.modal.body>

        <x-ui.modal.footer>
            <x-ui.modal.cancel-button :modal-name="'follow-up-'.$lead->id" />
            <x-ui.modal.submit-button>{{ __('Schedule') }}</x-ui.modal.submit-button>
        </x-ui.modal.footer>
    </form>
</x-modal>

<x-modal :name="'log-interaction-'.$lead->id" maxWidth="lg">
    <x-ui.modal.header
        :title="__('Log interaction')"
        :description="__('Record what happened and optionally schedule the next follow-up.')"
        :modal-name="'log-interaction-'.$lead->id"
    />

    <form method="POST" action="{{ route('tenant.leads.interactions.store', $lead) }}">
        @csrf
        <x-ui.modal.body>
            <x-ui.modal.section :title="__('Interaction')">
                <div class="space-y-4">
                    <div>
                        <x-ui.modal.field-label for="interaction_type_{{ $lead->id }}" :value="__('Type')" required />
                        <select
                            id="interaction_type_{{ $lead->id }}"
                            name="interaction_type"
                            required
                            class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy"
                        >
                            <option value="call">{{ __('Call') }}</option>
                            <option value="whatsapp">{{ __('WhatsApp') }}</option>
                            <option value="meeting">{{ __('Meeting') }}</option>
                            <option value="note">{{ __('Note') }}</option>
                        </select>
                    </div>
                    <div>
                        <x-ui.modal.field-label for="interaction_body_{{ $lead->id }}" :value="__('Notes')" />
                        <textarea
                            id="interaction_body_{{ $lead->id }}"
                            name="body"
                            rows="3"
                            class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy"
                            placeholder="{{ __('What was discussed?') }}"
                        ></textarea>
                    </div>
                </div>
            </x-ui.modal.section>
            <x-ui.modal.section :title="__('Optional next follow-up')">
                <div class="space-y-4">
                    <x-ui.datetime-picker name="next_follow_up_at" class="w-full" />
                    <input
                        type="text"
                        name="next_follow_up_notes"
                        class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy"
                        placeholder="{{ __('Follow-up notes') }}"
                    />
                </div>
            </x-ui.modal.section>
        </x-ui.modal.body>

        <x-ui.modal.footer>
            <x-ui.modal.cancel-button :modal-name="'log-interaction-'.$lead->id" />
            <x-ui.modal.submit-button>{{ __('Save') }}</x-ui.modal.submit-button>
        </x-ui.modal.footer>
    </form>
</x-modal>

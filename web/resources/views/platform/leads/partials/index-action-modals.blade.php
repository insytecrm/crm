@push('modals')
    <div
        x-data="{
            noteLeadId: @js(old('_lead_note_id')),
            noteActionUrl: '',
            setNoteLead(leadId) {
                this.noteLeadId = leadId;
                this.noteActionUrl = @js(str_replace('999999', '__ID__', route('platform.leads.notes.store', ['lead' => 999999]))).replace('__ID__', leadId);
            },
        }"
        x-on:open-lead-note.window="setNoteLead($event.detail.leadId); $dispatch('open-modal', 'add-lead-note-index')"
    >
        <x-modal name="add-lead-note-index" maxWidth="md" :show="$openNoteModal ?? false" focusable>
            <form method="POST" x-bind:action="noteActionUrl">
                @csrf
                <input type="hidden" name="_lead_note_id" x-bind:value="noteLeadId">
                <x-ui.modal.header :title="__('Add Note')" modal-name="add-lead-note-index" />
                <x-ui.modal.body>
                    <x-ui.modal.field-label for="index_note_body" :value="__('Note')" required />
                    <textarea id="index_note_body" name="body" rows="4" class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" required>{{ old('body') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('body')" />
                </x-ui.modal.body>
                <x-ui.modal.footer>
                    <x-ui.modal.cancel-button modal-name="add-lead-note-index" />
                    <x-ui.modal.submit-button>{{ __('Add Note') }}</x-ui.modal.submit-button>
                </x-ui.modal.footer>
            </form>
        </x-modal>
    </div>

    @include('platform.leads.partials.send-whatsapp-modal')
@endpush

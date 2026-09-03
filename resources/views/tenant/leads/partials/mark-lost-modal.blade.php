@props([
    'lead',
    'redirectToListing' => false,
])

<x-modal :name="'mark-lost-'.$lead->id" maxWidth="lg">
    <x-ui.modal.header
        :title="__('Mark Lead Lost')"
        :description="__('Select why this lead was lost and add a note before closing.')"
        :modal-name="'mark-lost-'.$lead->id"
    >
        <x-slot:icon>
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-2.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
        </x-slot:icon>
    </x-ui.modal.header>

    <form method="POST" action="{{ route('tenant.leads.mark-lost', $lead) }}">
        @csrf
        @if ($redirectToListing)
            <input type="hidden" name="redirect_to_listing" value="1">
        @endif

        <x-ui.modal.body>
            <x-ui.modal.section :title="__('Lost reason')">
                <p class="mb-3 text-sm text-slate-500">{{ __('Select all reasons that apply.') }}</p>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach (\App\Enums\LeadLostReason::casesForForm() as $reason)
                        <label class="flex items-start gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm transition-colors hover:border-slate-300 has-[:checked]:border-rose-300 has-[:checked]:bg-rose-50">
                            <input
                                type="checkbox"
                                name="lost_reasons[]"
                                value="{{ $reason->value }}"
                                class="mt-0.5 rounded border-slate-300 text-rose-600 focus:ring-rose-500"
                            >
                            <span>{{ $reason->label() }}</span>
                        </label>
                    @endforeach
                </div>
                @error('lost_reasons')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
                @error('lost_reasons.*')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </x-ui.modal.section>

            <x-ui.modal.section :title="__('Additional note')">
                <div>
                    <x-ui.modal.field-label for="closing_notes_{{ $lead->id }}" :value="__('Additional note')" required />
                    <textarea
                        id="closing_notes_{{ $lead->id }}"
                        name="closing_notes"
                        rows="4"
                        required
                        class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy"
                        placeholder="{{ __('Add details about why this lead was lost') }}"
                    >{{ old('closing_notes') }}</textarea>
                    @error('closing_notes')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </x-ui.modal.section>
        </x-ui.modal.body>

        <x-ui.modal.footer>
            <x-ui.modal.cancel-button :modal-name="'mark-lost-'.$lead->id" />
            <x-ui.button type="submit" variant="destructive" class="bg-red-600 text-white hover:bg-red-700">{{ __('Mark Lost') }}</x-ui.button>
        </x-ui.modal.footer>
    </form>
</x-modal>

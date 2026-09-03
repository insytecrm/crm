<x-modal :name="'close-lead-'.$lead->id" maxWidth="lg">
    <div class="p-6" x-data="{ reason: '{{ \App\Enums\LeadClosingReason::Converted->value }}' }">
        <h2 class="text-lg font-bold text-black">{{ __('Close Lead') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('Select a reason for closing this lead.') }}</p>
        <form method="POST" action="{{ route('tenant.leads.close', $lead) }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <x-input-label :value="__('Closing Reason')" />
                <x-ui.combobox
                    name="closing_reason"
                    :options="collect(\App\Enums\LeadClosingReason::cases())->map(fn ($reason) => ['value' => $reason->value, 'label' => $reason->label()])->all()"
                    :value="\App\Enums\LeadClosingReason::Converted->value"
                    :searchable="false"
                    required
                    x-on:selected="reason = $event.detail"
                />
            </div>
            <label x-show="reason === 'converted'" x-cloak class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="continue_to_booking" value="1" class="rounded border-slate-300 text-black focus:ring-navy">
                {{ __('Continue to Create Booking') }}
            </label>
            <div class="flex justify-end gap-2">
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', 'close-lead-{{ $lead->id }}')">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button type="submit" variant="destructive">{{ __('Close Lead') }}</x-ui.button>
            </div>
        </form>
    </div>
</x-modal>

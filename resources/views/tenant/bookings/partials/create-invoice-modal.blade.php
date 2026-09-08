<x-modal :name="'create-invoice-'.$booking->id" maxWidth="md">
    <div class="p-6">
        <h2 class="text-lg font-bold text-black">{{ __('Create Invoice') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('Create an invoice for :property, Unit :unit.', ['property' => $booking->property->project_name, 'unit' => $booking->unit_number]) }}</p>

        <form method="POST" action="{{ route('tenant.bookings.invoice.store', ['booking' => $booking]) }}" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="_open_modal" value="create-invoice-{{ $booking->id }}">

            <dl class="rounded-lg border border-slate-100 bg-slate-50 p-3 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Agreement Value') }}</dt>
                    <dd class="font-medium text-black">₹{{ number_format($booking->agreement_value) }}</dd>
                </div>
                <div class="mt-2 flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Payout (%)') }}</dt>
                    <dd class="font-medium text-black">{{ $booking->payout_percent ?? '—' }}%</dd>
                </div>
                <div class="mt-2 flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Invoice Amount') }}</dt>
                    <dd class="font-medium text-black">₹{{ number_format($booking->payout_amount ?? 0) }}</dd>
                </div>
                <div class="mt-2 flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Agreement Date') }}</dt>
                    <dd class="font-medium text-black">{{ $booking->agreement_date?->format('M j, Y') }}</dd>
                </div>
            </dl>

            <div>
                <x-input-label for="invoice_date_{{ $booking->id }}" :value="__('Invoice Date')" />
                <x-ui.datetime-picker
                    id="invoice_date_{{ $booking->id }}"
                    name="invoice_date"
                    mode="date"
                    :value="old('invoice_date', now()->toDateString())"
                    required
                />
                @error('invoice_date')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', 'create-invoice-{{ $booking->id }}')">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button type="submit" variant="default">{{ __('Create Invoice') }}</x-ui.button>
            </div>
        </form>
    </div>
</x-modal>

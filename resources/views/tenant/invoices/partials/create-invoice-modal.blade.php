@php
    $bookingOptions = $billableBookings
        ->map(function (\App\Models\Booking $booking): array {
            $parts = array_filter([
                $booking->lead?->name,
                $booking->property?->project_name,
                __('Unit :unit', ['unit' => $booking->unit_number]),
            ]);

            return [
                'value' => (string) $booking->id,
                'label' => implode(' · ', $parts),
            ];
        })
        ->all();
@endphp

<x-modal name="create-invoice" maxWidth="md">
    <div class="p-6">
        <h2 class="text-lg font-bold text-black">{{ __('Create Invoice') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('Select a booking with an agreement that does not yet have an invoice.') }}</p>

        @if ($billableBookings->isEmpty())
            <p class="mt-4 rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                {{ __('No bookings are ready to invoice. Mark an agreement first.') }}
            </p>
            <div class="mt-4 flex justify-end">
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', 'create-invoice')">{{ __('Close') }}</x-ui.button>
            </div>
        @else
            <form method="POST" action="{{ route('tenant.invoices.store') }}" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="_open_modal" value="create-invoice">

                <div>
                    <x-input-label for="invoice_booking_id" :value="__('Booking')" />
                    <x-ui.select
                        id="invoice_booking_id"
                        name="booking_id"
                        :value="old('booking_id')"
                        :options="$bookingOptions"
                        :placeholder="__('Select a booking')"
                        required
                    />
                    @error('booking_id')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-input-label for="invoice_date" :value="__('Invoice Date')" />
                    <x-ui.datetime-picker
                        id="invoice_date"
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
                    <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', 'create-invoice')">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button type="submit" variant="default">{{ __('Create Invoice') }}</x-ui.button>
                </div>
            </form>
        @endif
    </div>
</x-modal>

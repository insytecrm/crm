<x-modal :name="'mark-agreement-'.$booking->id" maxWidth="md">
    @php
        $defaultPayoutPercent = old('payout_percent', $booking->property->payout_percent ?? 0);
        $defaultAgreementValue = old('agreement_value', $booking->agreement_value);
        $defaultPayoutAmount = old(
            'payout_amount',
            \App\Models\Booking::calculatePayoutAmount((int) $defaultAgreementValue, $defaultPayoutPercent)
        );
    @endphp

    <div
        class="p-6"
        x-data="{
            agreementValue: {{ (int) $defaultAgreementValue }},
            payoutPercent: {{ (float) $defaultPayoutPercent }},
            payoutAmount: {{ (int) $defaultPayoutAmount }},
            recalculatePayout() {
                this.payoutAmount = Math.round(this.agreementValue * (this.payoutPercent / 100))
            },
        }"
    >
        <h2 class="text-lg font-bold text-black">{{ __('Mark Agreement') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('Record the agreement details for :property, Unit :unit.', ['property' => $booking->property->project_name, 'unit' => $booking->unit_number]) }}</p>

        <form method="POST" action="{{ route('tenant.bookings.agreement.store', $booking) }}" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="_open_modal" value="mark-agreement-{{ $booking->id }}">

            <div>
                <x-input-label for="agreement_date_{{ $booking->id }}" :value="__('Agreement Date')" />
                <x-ui.datetime-picker
                    id="agreement_date_{{ $booking->id }}"
                    name="agreement_date"
                    mode="date"
                    :value="old('agreement_date', now()->toDateString())"
                    required
                />
                @error('agreement_date')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-input-label for="agreement_value_{{ $booking->id }}" :value="__('Agreement Value (₹)')" />
                <x-auth.icon-input
                    id="agreement_value_{{ $booking->id }}"
                    name="agreement_value"
                    type="number"
                    min="1"
                    x-model.number="agreementValue"
                    @input="recalculatePayout()"
                    required
                />
                @error('agreement_value')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="payout_percent_{{ $booking->id }}" :value="__('Payout (%)')" />
                    <x-auth.icon-input
                        id="payout_percent_{{ $booking->id }}"
                        name="payout_percent"
                        type="number"
                        step="0.01"
                        min="0"
                        max="100"
                        x-model.number="payoutPercent"
                        @input="recalculatePayout()"
                        required
                    />
                    @error('payout_percent')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-input-label for="payout_amount_{{ $booking->id }}" :value="__('Payout Amount (₹)')" />
                    <x-auth.icon-input
                        id="payout_amount_{{ $booking->id }}"
                        name="payout_amount"
                        type="number"
                        min="0"
                        x-model.number="payoutAmount"
                        required
                    />
                    @error('payout_amount')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', 'mark-agreement-{{ $booking->id }}')">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button type="submit" variant="default">{{ __('Mark Agreement') }}</x-ui.button>
            </div>
        </form>
    </div>
</x-modal>

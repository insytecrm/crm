<x-modal :name="'invoice-'.$invoice->id" maxWidth="2xl">
    <div class="p-6" x-data="{ editing: @js($errors->any() && old('_open_modal') === 'invoice-'.$invoice->id) }">
        <div x-show="!editing">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-black">{{ __('Invoice Details') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $invoice->invoice_number ?? \App\Models\Booking::invoiceNumberFor($invoice->id) }} · {{ $invoice->property->project_name }}</p>
                </div>
                <span class="inline-flex shrink-0 items-center rounded-full bg-sky-50 px-2.5 py-1 text-xs font-medium text-sky-700">
                    {{ $invoice->invoice_date?->format('M j, Y') }}
                </span>
            </div>

            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Invoice Number') }}</dt>
                    <dd class="mt-1 text-sm text-black">{{ $invoice->invoice_number ?? \App\Models\Booking::invoiceNumberFor($invoice->id) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Property') }}</dt>
                    <dd class="mt-1 text-sm text-black">{{ $invoice->property->project_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Configuration') }}</dt>
                    <dd class="mt-1 text-sm text-black">{{ $invoice->configuration_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Unit Number') }}</dt>
                    <dd class="mt-1 text-sm text-black">{{ $invoice->unit_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Agreement Value') }}</dt>
                    <dd class="mt-1 text-sm text-black">₹{{ number_format($invoice->agreement_value) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Payout (%)') }}</dt>
                    <dd class="mt-1 text-sm text-black">{{ $invoice->payout_percent ?? '—' }}%</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Invoice Amount') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-black">₹{{ number_format($invoice->payout_amount ?? 0) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Agreement Date') }}</dt>
                    <dd class="mt-1 text-sm text-black">{{ $invoice->agreement_date?->format('M j, Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Invoice Date') }}</dt>
                    <dd class="mt-1 text-sm text-black">{{ $invoice->invoice_date?->format('M j, Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Lead') }}</dt>
                    <dd class="mt-1 text-sm text-black">
                        @if ($invoice->lead)
                            <x-tenant.lead-link :lead="$invoice->lead" />
                        @else
                            —
                        @endif
                    </dd>
                </div>
                @if ($invoice->invoice_notes)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Notes') }}</dt>
                        <dd class="mt-1 whitespace-pre-wrap text-sm text-slate-700">{{ $invoice->invoice_notes }}</dd>
                    </div>
                @endif
            </dl>

            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <x-ui.button type="button" variant="outline" :href="route('tenant.invoices.pdf', $invoice)">
                    {{ __('Download PDF') }}
                </x-ui.button>
                <x-ui.button type="button" variant="outline" @click="editing = true">{{ __('Edit Invoice') }}</x-ui.button>
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', 'invoice-{{ $invoice->id }}')">{{ __('Close') }}</x-ui.button>
            </div>
        </div>

        <div x-show="editing" x-cloak
            x-data="{
                agreementValue: {{ (int) old('agreement_value', $invoice->agreement_value) }},
                payoutPercent: {{ (float) old('payout_percent', $invoice->payout_percent ?? 0) }},
                payoutAmount: {{ (int) old('payout_amount', $invoice->payout_amount ?? 0) }},
                recalculatePayout() {
                    this.payoutAmount = Math.round(this.agreementValue * (this.payoutPercent / 100))
                },
            }"
        >
            <h2 class="text-lg font-bold text-black">{{ __('Edit Invoice') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $invoice->property->project_name }} · {{ __('Unit') }} {{ $invoice->unit_number }}</p>

            <form method="POST" action="{{ route('tenant.invoices.update', $invoice) }}" class="mt-4 space-y-4">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_open_modal" value="invoice-{{ $invoice->id }}">

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="invoice_number_{{ $invoice->id }}" :value="__('Invoice Number')" />
                        <x-auth.icon-input
                            id="invoice_number_{{ $invoice->id }}"
                            name="invoice_number"
                            :value="old('invoice_number', $invoice->invoice_number ?? \App\Models\Booking::invoiceNumberFor($invoice->id))"
                            required
                        />
                        @error('invoice_number')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-input-label for="invoice_date_{{ $invoice->id }}" :value="__('Invoice Date')" />
                        <x-ui.datetime-picker
                            id="invoice_date_{{ $invoice->id }}"
                            name="invoice_date"
                            mode="date"
                            :value="old('invoice_date', $invoice->invoice_date?->toDateString())"
                            required
                        />
                        @error('invoice_date')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-input-label for="agreement_value_{{ $invoice->id }}" :value="__('Agreement Value (₹)')" />
                        <x-auth.icon-input
                            id="agreement_value_{{ $invoice->id }}"
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
                    <div>
                        <x-input-label for="payout_percent_{{ $invoice->id }}" :value="__('Payout (%)')" />
                        <x-auth.icon-input
                            id="payout_percent_{{ $invoice->id }}"
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
                    <div class="sm:col-span-2">
                        <x-input-label for="payout_amount_{{ $invoice->id }}" :value="__('Invoice Amount (₹)')" />
                        <x-auth.icon-input
                            id="payout_amount_{{ $invoice->id }}"
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
                    <div class="sm:col-span-2">
                        <x-input-label for="invoice_notes_{{ $invoice->id }}" :value="__('Notes')" />
                        <textarea
                            id="invoice_notes_{{ $invoice->id }}"
                            name="invoice_notes"
                            rows="3"
                            class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy"
                            placeholder="{{ __('Optional invoice notes') }}"
                        >{{ old('invoice_notes', $invoice->invoice_notes) }}</textarea>
                        @error('invoice_notes')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <x-ui.button type="button" variant="outline" @click="editing = false">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button type="submit" variant="default">{{ __('Save Changes') }}</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</x-modal>

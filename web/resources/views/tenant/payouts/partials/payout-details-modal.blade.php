<x-modal :name="'payout-'.$payout->id" maxWidth="2xl">
    <div class="p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-black">{{ __('Payout Details') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $payout->property->project_name }} · {{ __('Unit') }} {{ $payout->unit_number }}</p>
            </div>
            <div class="flex shrink-0 flex-wrap justify-end gap-1.5">
                @if ($payout->hasPaidPayout())
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                        {{ __('Paid') }} · {{ $payout->payout_paid_at->format('M j, Y') }}
                    </span>
                @else
                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">
                        {{ __('Pending Payment') }}
                    </span>
                @endif
                @if ($payout->hasInvoice())
                    <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-1 text-xs font-medium text-sky-700">
                        {{ __('Invoiced') }} · {{ $payout->invoice_number ?? \App\Models\Booking::invoiceNumberFor($payout->id) }}
                    </span>
                @endif
            </div>
        </div>

        <dl class="mt-6 grid gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Property') }}</dt>
                <dd class="mt-1 text-sm text-black">{{ $payout->property->project_name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Configuration') }}</dt>
                <dd class="mt-1 text-sm text-black">{{ $payout->configuration_name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Unit Number') }}</dt>
                <dd class="mt-1 text-sm text-black">{{ $payout->unit_number }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Lead') }}</dt>
                <dd class="mt-1 text-sm text-black">
                    @if ($payout->lead)
                        <x-tenant.lead-link :lead="$payout->lead" />
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Agreement Date') }}</dt>
                <dd class="mt-1 text-sm text-black">{{ $payout->agreement_date?->format('M j, Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Booking Date') }}</dt>
                <dd class="mt-1 text-sm text-black">{{ $payout->booking_date->format('M j, Y') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Agreement Value') }}</dt>
                <dd class="mt-1 text-sm text-black">₹{{ number_format($payout->agreement_value) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Payout (%)') }}</dt>
                <dd class="mt-1 text-sm text-black">{{ $payout->payout_percent ?? '—' }}%</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Payout Amount') }}</dt>
                <dd class="mt-1 text-sm font-semibold text-black">₹{{ number_format($payout->payout_amount ?? 0) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Payment Status') }}</dt>
                <dd class="mt-1 text-sm text-black">
                    @if ($payout->hasPaidPayout())
                        {{ __('Paid on :date', ['date' => $payout->payout_paid_at->format('M j, Y g:i A')]) }}
                    @else
                        {{ __('Pending') }}
                    @endif
                </dd>
            </div>
            @if ($payout->hasInvoice())
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Invoice Date') }}</dt>
                    <dd class="mt-1 text-sm text-black">{{ $payout->invoice_date?->format('M j, Y') ?? '—' }}</dd>
                </div>
            @endif
        </dl>

        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            @if ($payout->canMarkPayoutPaid())
                <form method="POST" action="{{ route('tenant.payouts.mark-paid', $payout) }}">
                    @csrf
                    <x-ui.button type="submit" variant="default">{{ __('Mark Paid') }}</x-ui.button>
                </form>
            @endif
            <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', 'payout-{{ $payout->id }}')">{{ __('Close') }}</x-ui.button>
        </div>
    </div>
</x-modal>

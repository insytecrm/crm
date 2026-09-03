<x-modal :name="'booking-'.$booking->id" maxWidth="2xl">
    <div class="p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-black">{{ __('Booking Details') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $booking->property->project_name }} · {{ __('Unit') }} {{ $booking->unit_number }}</p>
            </div>
            <div class="flex shrink-0 flex-wrap justify-end gap-1.5">
                @if ($booking->hasAgreement())
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                        {{ __('Agreement') }} · {{ $booking->agreement_date->format('M j, Y') }}
                    </span>
                @endif
                @if ($booking->hasInvoice())
                    <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-1 text-xs font-medium text-sky-700">
                        {{ __('Invoiced') }} · {{ $booking->invoice_date?->format('M j, Y') }}
                    </span>
                @endif
            </div>
        </div>

        <dl class="mt-6 grid gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Property') }}</dt>
                <dd class="mt-1 text-sm text-black">{{ $booking->property->project_name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Configuration') }}</dt>
                <dd class="mt-1 text-sm text-black">{{ $booking->configuration_name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Unit Number') }}</dt>
                <dd class="mt-1 text-sm text-black">{{ $booking->unit_number }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Agreement Value') }}</dt>
                <dd class="mt-1 text-sm text-black">₹{{ number_format($booking->agreement_value) }}</dd>
            </div>
            @if ($booking->hasAgreement())
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Payout (%)') }}</dt>
                    <dd class="mt-1 text-sm text-black">{{ $booking->payout_percent ?? '—' }}%</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Payout Amount') }}</dt>
                    <dd class="mt-1 text-sm text-black">₹{{ number_format($booking->payout_amount ?? 0) }}</dd>
                </div>
            @endif
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Booking Date') }}</dt>
                <dd class="mt-1 text-sm text-black">{{ $booking->booking_date->format('M j, Y') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Lead') }}</dt>
                <dd class="mt-1 text-sm text-black">
                    @if ($booking->lead)
                        <x-tenant.lead-link :lead="$booking->lead" />
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Agreement Date') }}</dt>
                <dd class="mt-1 text-sm text-black">{{ $booking->agreement_date?->format('M j, Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Invoice Date') }}</dt>
                <dd class="mt-1 text-sm text-black">{{ $booking->invoice_date?->format('M j, Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Created By') }}</dt>
                <dd class="mt-1 text-sm text-black">{{ $booking->createdBy?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Created At') }}</dt>
                <dd class="mt-1 text-sm text-black">{{ $booking->created_at?->format('M j, Y g:i A') }}</dd>
            </div>
        </dl>

        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            @if ($booking->canMarkAgreement())
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', 'booking-{{ $booking->id }}'); $dispatch('open-modal', 'mark-agreement-{{ $booking->id }}')">
                    {{ __('Mark Agreement') }}
                </x-ui.button>
            @endif
            @if ($booking->canCreateInvoice())
                <x-ui.button type="button" variant="default" @click="$dispatch('close-modal', 'booking-{{ $booking->id }}'); $dispatch('open-modal', 'create-invoice-{{ $booking->id }}')">
                    {{ __('Create Invoice') }}
                </x-ui.button>
            @endif
            <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', 'booking-{{ $booking->id }}')">{{ __('Close') }}</x-ui.button>
        </div>
    </div>
</x-modal>

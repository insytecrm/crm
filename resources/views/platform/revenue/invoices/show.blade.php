<x-app-layout :title="'#'.$invoice->number.' | InSyte CRM'">
    <x-platform.billing-shell section="invoices">
        <div class="mb-4">
            <a href="{{ route('platform.revenue.invoices') }}" class="text-sm font-semibold text-navy hover:underline">← {{ __('Invoices') }}</a>
        </div>

        <x-platform.page-header :title="'#'.$invoice->number">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    <x-platform.status-badge :status="$invoice->status" />
                    <x-ui.button variant="outline" :href="route('platform.revenue.invoices.download', $invoice)">{{ __('Download PDF') }}</x-ui.button>
                    <form method="POST" action="{{ route('platform.revenue.invoices.send', $invoice) }}">
                        @csrf
                        <x-ui.button type="submit" variant="outline">{{ __('Send Invoice') }}</x-ui.button>
                    </form>
                </div>
            </x-slot:actions>
        </x-platform.page-header>

        <x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

        <div class="grid gap-4 lg:grid-cols-2">
            <x-platform.panel :title="__('Invoice Information')" compact>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">{{ __('Invoice Number') }}</dt>
                        <dd class="mt-1 font-medium text-black">#{{ $invoice->number }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Issue Date') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $invoice->issued_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Due Date') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $invoice->due_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Status') }}</dt>
                        <dd class="mt-1"><x-platform.status-badge :status="$invoice->status" /></dd>
                    </div>
                </dl>
            </x-platform.panel>

            <x-platform.panel :title="__('Billed To')" compact>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-slate-500">{{ __('Channel Partner') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $invoice->tenant?->name ?? $invoice->billed_to_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Owner / Billing Contact') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $invoice->billed_to_contact ?: ($invoice->tenant?->getAttribute('owner_name') ?: '—') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Email') }}</dt>
                        <dd class="mt-1 font-medium text-black">
                            @php($email = $invoice->billed_to_email ?: $invoice->tenant?->email)
                            @if ($email)
                                <a href="mailto:{{ $email }}" class="text-navy hover:underline">{{ $email }}</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </x-platform.panel>

            <x-platform.panel :title="__('Plan Details')" compact>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">{{ __('Plan') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $invoice->plan?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Billing Cycle') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $invoice->billing_cycle?->label() ?? '—' }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-slate-500">{{ __('Subscription Period') }}</dt>
                        <dd class="mt-1 font-medium text-black">
                            {{ $invoice->period_start?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}
                            –
                            {{ $invoice->period_end?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Price') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ \App\Support\Platform\BillingMoney::format((int) $invoice->subtotal) }}</dd>
                    </div>
                </dl>
            </x-platform.panel>

            <x-platform.panel :title="__('Amount Summary')" compact>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Subtotal') }}</dt><dd class="font-medium text-black">{{ \App\Support\Platform\BillingMoney::format((int) $invoice->subtotal) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Discount') }}</dt><dd class="font-medium text-black">{{ \App\Support\Platform\BillingMoney::format((int) $invoice->discount_amount) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Tax') }}</dt><dd class="font-medium text-black">{{ \App\Support\Platform\BillingMoney::format((int) $invoice->tax_amount) }}</dd></div>
                    <div class="flex justify-between gap-3 border-t border-slate-100 pt-2"><dt class="font-semibold text-black">{{ __('Total') }}</dt><dd class="font-bold text-black">{{ \App\Support\Platform\BillingMoney::format((int) $invoice->total) }}</dd></div>
                </dl>
            </x-platform.panel>
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <x-platform.panel :title="__('Payment Status')" compact>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">{{ __('Status') }}</dt>
                        <dd class="mt-1"><x-platform.status-badge :status="$invoice->status" /></dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Payment Date') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ ($invoice->paid_at ?? $payment?->payment_date)?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Payment Method') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $payment?->payment_method ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Transaction ID') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $payment?->transaction_id ?? '—' }}</dd>
                    </div>
                </dl>
            </x-platform.panel>

            <x-platform.panel :title="__('Invoice Timeline')" compact>
                @if ($timeline === [])
                    <p class="text-sm text-slate-500">{{ __('No timeline events yet.') }}</p>
                @else
                    <ol class="space-y-3">
                        @foreach ($timeline as $index => $step)
                            <li>
                                <p class="text-sm font-semibold text-black">{{ $step['label'] }}</p>
                                <p class="text-xs text-slate-500">{{ $step['at'] }}</p>
                                @if ($index < count($timeline) - 1)
                                    <div class="my-2 ml-1 h-4 border-l border-slate-200"></div>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-platform.panel>
        </div>
    </x-platform.billing-shell>
</x-app-layout>

<x-app-layout :title="\App\Support\Platform\BillingMoney::format((int) $payment->amount).' | InSyte CRM'">
    <x-platform.billing-shell section="payments">
        <div class="mb-4">
            <a href="{{ route('platform.revenue.payments') }}" class="text-sm font-semibold text-navy hover:underline">← {{ __('Payments') }}</a>
        </div>

        <x-platform.page-header :title="\App\Support\Platform\BillingMoney::format((int) $payment->amount)">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    <x-platform.status-badge :status="$payment->status" />
                    <p class="text-sm text-slate-500">
                        {{ __('Channel Partner:') }}
                        <span class="font-semibold text-black">{{ $payment->tenant?->name ?? __('Unknown partner') }}</span>
                    </p>
                </div>
            </x-slot:actions>
        </x-platform.page-header>

        <x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

        <div class="grid gap-4 lg:grid-cols-2">
            <x-platform.panel :title="__('Payment Information')" compact>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">{{ __('Amount') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ \App\Support\Platform\BillingMoney::format((int) $payment->amount) }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Payment Status') }}</dt>
                        <dd class="mt-1"><x-platform.status-badge :status="$payment->status" /></dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Payment Date') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $payment->payment_date?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Payment Method') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $payment->payment_method ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Transaction ID') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $payment->transaction_id ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Invoice') }}</dt>
                        <dd class="mt-1 font-medium text-black">
                            @if ($payment->invoice)
                                <a href="{{ route('platform.revenue.invoices.show', $payment->invoice) }}" class="text-navy hover:underline">#{{ $payment->invoice->number }}</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Plan') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $payment->plan?->name ?? '—' }}</dd>
                    </div>
                </dl>
            </x-platform.panel>

            <x-platform.panel :title="__('Payment Timeline')" compact>
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

        <details class="mt-4 rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
            <summary class="cursor-pointer text-sm font-semibold text-black">{{ __('Technical Details') }}</summary>
            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-slate-500">{{ __('Gateway') }}</dt>
                    <dd class="mt-1 font-medium text-black">{{ $payment->gateway ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Gateway Transaction ID') }}</dt>
                    <dd class="mt-1 font-medium text-black">{{ $payment->gateway_transaction_id ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Response Code') }}</dt>
                    <dd class="mt-1 font-medium text-black">{{ $payment->response_code ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Failure Reason') }}</dt>
                    <dd class="mt-1 font-medium text-black">{{ $payment->failure_reason ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Webhook Status') }}</dt>
                    <dd class="mt-1 font-medium text-black">{{ $payment->webhook_status ?? '—' }}</dd>
                </div>
            </dl>
        </details>
    </x-platform.billing-shell>
</x-app-layout>

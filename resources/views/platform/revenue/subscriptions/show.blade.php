<x-app-layout :title="($subscription->tenant?->name ?? __('Subscription')) . ' | InSyte CRM'">
    <x-platform.billing-shell section="subscriptions">
        <div class="mb-4">
            <a href="{{ route('platform.revenue.subscriptions') }}" class="text-sm font-semibold text-navy hover:underline">← {{ __('Subscriptions') }}</a>
        </div>

        <x-platform.page-header
            :title="$subscription->tenant?->name ?? __('Unknown partner')"
            :description="$subscription->plan?->name"
        >
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    <p class="text-sm font-semibold text-black">{{ $priceLabel }}</p>
                    <x-platform.status-badge :status="$subscription->status" />
                    <x-ui.button variant="outline" href="#change-plan">{{ __('Change Plan') }}</x-ui.button>
                    <x-ui.button variant="outline" href="#extend-trial">{{ __('Extend Trial') }}</x-ui.button>
                    @if ($subscription->status === \App\Enums\SubscriptionStatus::Paused)
                        <form method="POST" action="{{ route('platform.revenue.subscriptions.resume', $subscription) }}">
                            @csrf
                            <x-ui.button type="submit" variant="outline">{{ __('Resume') }}</x-ui.button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('platform.revenue.subscriptions.pause', $subscription) }}">
                            @csrf
                            <x-ui.button type="submit" variant="outline">{{ __('Pause') }}</x-ui.button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('platform.revenue.subscriptions.cancel', $subscription) }}" onsubmit="return confirm(@js(__('Cancel this subscription?')))">
                        @csrf
                        <x-ui.button type="submit" variant="destructive">{{ __('Cancel') }}</x-ui.button>
                    </form>
                </div>
            </x-slot:actions>
        </x-platform.page-header>

        <x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

        <div class="grid gap-4 lg:grid-cols-2">
            <x-platform.panel :title="__('Subscription')" compact>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">{{ __('Started') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $subscription->started_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Next Billing') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $subscription->next_billing_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Billing Cycle') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $subscription->billing_cycle?->label() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Status') }}</dt>
                        <dd class="mt-1"><x-platform.status-badge :status="$subscription->status" /></dd>
                    </div>
                </dl>
            </x-platform.panel>

            <x-platform.panel :title="__('Billing Information')" compact>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">{{ __('Current Plan') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $subscription->plan?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Current Price') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $priceLabel }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Next Billing') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $subscription->next_billing_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Payment Status') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $paymentStatus }}</dd>
                    </div>
                </dl>
            </x-platform.panel>
        </div>

        <x-platform.panel class="mt-4" :title="__('Billing History')" compact>
            @if ($invoices->isEmpty())
                <p class="text-sm text-slate-500">{{ __('No invoices for this subscription yet.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead>
                            <tr class="border-b border-slate-100 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-3">{{ __('Date') }}</th>
                                <th class="px-3 py-3">{{ __('Invoice') }}</th>
                                <th class="px-3 py-3">{{ __('Amount') }}</th>
                                <th class="px-3 py-3">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $invoice)
                                <tr class="border-b border-slate-50">
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $invoice->issued_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</td>
                                    <td class="px-3 py-3 text-sm font-medium text-black">
                                        <a href="{{ route('platform.revenue.invoices.show', $invoice) }}" class="hover:text-navy">#{{ $invoice->number }}</a>
                                    </td>
                                    <td class="px-3 py-3 text-sm text-black">{{ \App\Support\Platform\BillingMoney::format((int) $invoice->total) }}</td>
                                    <td class="px-3 py-3"><x-platform.status-badge :status="$invoice->status" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-platform.panel>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <x-platform.panel id="change-plan" :title="__('Change Plan')" compact>
                <form method="POST" action="{{ route('platform.revenue.subscriptions.change-plan', $subscription) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Plan') }}</label>
                        <select name="plan_id" class="w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" required>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected($subscription->plan_id === $plan->id)>{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Billing Cycle') }}</label>
                        <select name="billing_cycle" class="w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" required>
                            @foreach (\App\Enums\BillingCycle::cases() as $cycle)
                                <option value="{{ $cycle->value }}" @selected($subscription->billing_cycle === $cycle)>{{ $cycle->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-ui.button type="submit" variant="default">{{ __('Update Plan') }}</x-ui.button>
                </form>
            </x-platform.panel>

            <x-platform.panel id="extend-trial" :title="__('Extend Trial')" compact>
                <form method="POST" action="{{ route('platform.revenue.subscriptions.extend-trial', $subscription) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Days') }}</label>
                        <input type="number" name="days" min="1" max="90" value="7" class="w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" required>
                    </div>
                    <x-ui.button type="submit" variant="outline">{{ __('Extend Trial') }}</x-ui.button>
                </form>
            </x-platform.panel>
        </div>
    </x-platform.billing-shell>
</x-app-layout>

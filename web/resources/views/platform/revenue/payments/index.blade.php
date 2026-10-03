<x-app-layout :title="__('Payments') . ' | InSyte CRM'">
    <x-platform.billing-shell section="payments">
        <x-platform.page-header
            :title="__('Payments')"
            :description="__('Track successful, pending, failed, and refunded payments.')"
        />

        <x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

        <form method="GET" action="{{ route('platform.revenue.payments') }}" class="mb-4 flex flex-wrap gap-2">
            <input
                type="search"
                name="search"
                value="{{ $filters['search'] }}"
                placeholder="{{ __('Search partner or transaction...') }}"
                class="min-w-[16rem] flex-1 rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy"
            >
            <select name="status" class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" onchange="this.form.submit()">
                @foreach ($statusOptions as $option)
                    <option value="{{ $option['value'] }}" @selected($filters['status'] === $option['value'])>{{ $option['label'] }}</option>
                @endforeach
            </select>
            <x-ui.button type="submit" variant="outline">{{ __('Search') }}</x-ui.button>
        </form>

        <x-platform.panel compact>
            @if ($payments->isEmpty())
                <p class="text-sm text-slate-500">{{ __('No payments match these filters.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead>
                            <tr class="border-b border-slate-100 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-3">{{ __('Channel Partner') }}</th>
                                <th class="px-3 py-3">{{ __('Amount') }}</th>
                                <th class="px-3 py-3">{{ __('Payment Type') }}</th>
                                <th class="px-3 py-3">{{ __('Invoice') }}</th>
                                <th class="px-3 py-3">{{ __('Date') }}</th>
                                <th class="px-3 py-3">{{ __('Status') }}</th>
                                <th class="px-3 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                <tr class="border-b border-slate-50">
                                    <td class="px-3 py-3 text-sm font-medium text-black">
                                        <a href="{{ route('platform.revenue.payments.show', $payment) }}" class="hover:text-navy">
                                            {{ $payment->tenant?->name ?? __('Unknown partner') }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-3 text-sm text-black">{{ \App\Support\Platform\BillingMoney::format((int) $payment->amount) }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $payment->type->label() }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">
                                        @if ($payment->invoice)
                                            <a href="{{ route('platform.revenue.invoices.show', $payment->invoice) }}" class="hover:text-navy">#{{ $payment->invoice->number }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ ($payment->payment_date ?? $payment->created_at)?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</td>
                                    <td class="px-3 py-3"><x-platform.status-badge :status="$payment->status" /></td>
                                    <td class="px-3 py-3 text-end">
                                        <x-ui.action-icon-group>
                                            <x-ui.action-icon
                                                icon="view"
                                                :href="route('platform.revenue.payments.show', $payment)"
                                                :title="__('View')"
                                            />
                                            @if ($payment->invoice)
                                                <x-ui.action-icon
                                                    icon="invoice"
                                                    :href="route('platform.revenue.invoices.show', $payment->invoice)"
                                                    :title="__('View Invoice')"
                                                />
                                            @endif
                                        </x-ui.action-icon-group>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $payments->links() }}</div>
            @endif
        </x-platform.panel>
    </x-platform.billing-shell>
</x-app-layout>

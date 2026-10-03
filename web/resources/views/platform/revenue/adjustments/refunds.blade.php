<x-app-layout :title="__('Refunds') . ' | InSyte CRM'">
    <x-platform.billing-shell section="adjustments">
        <div class="mb-4">
            <a href="{{ route('platform.revenue.adjustments') }}" class="text-sm font-semibold text-navy hover:underline">← {{ __('Adjustments') }}</a>
        </div>

        <x-platform.page-header
            :title="__('Refunds')"
            :description="__('Track money returned to Channel Partners.')"
        />

        <x-platform.panel compact>
            @if ($refunds->isEmpty())
                <p class="text-sm text-slate-500">{{ __('No refunds recorded yet.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead>
                            <tr class="border-b border-slate-100 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-3">{{ __('Channel Partner') }}</th>
                                <th class="px-3 py-3">{{ __('Payment') }}</th>
                                <th class="px-3 py-3">{{ __('Refund Amount') }}</th>
                                <th class="px-3 py-3">{{ __('Reason') }}</th>
                                <th class="px-3 py-3">{{ __('Date') }}</th>
                                <th class="px-3 py-3">{{ __('Status') }}</th>
                                <th class="px-3 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($refunds as $refund)
                                <tr class="border-b border-slate-50">
                                    <td class="px-3 py-3 text-sm font-medium text-black">{{ $refund->tenant?->name ?? '—' }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">
                                        @if ($refund->payment)
                                            <a href="{{ route('platform.revenue.payments.show', $refund->payment) }}" class="hover:text-navy">
                                                {{ \App\Support\Platform\BillingMoney::format((int) $refund->payment->amount) }} {{ __('Payment') }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-sm text-black">{{ \App\Support\Platform\BillingMoney::format((int) $refund->amount) }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $refund->reason }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ ($refund->refunded_at ?? $refund->created_at)?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</td>
                                    <td class="px-3 py-3"><x-platform.status-badge :status="$refund->status" /></td>
                                    <td class="px-3 py-3 text-end">
                                        @if ($refund->payment)
                                            <x-ui.action-icon-group>
                                                <x-ui.action-icon
                                                    icon="view"
                                                    :href="route('platform.revenue.payments.show', $refund->payment)"
                                                    :title="__('View')"
                                                />
                                                <x-ui.action-icon
                                                    icon="invoice"
                                                    :href="route('platform.revenue.payments.show', $refund->payment)"
                                                    :title="__('View Payment')"
                                                />
                                            </x-ui.action-icon-group>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $refunds->links() }}</div>
            @endif
        </x-platform.panel>
    </x-platform.billing-shell>
</x-app-layout>

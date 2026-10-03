<x-app-layout :title="__('Discounts') . ' | InSyte CRM'">
    <x-platform.billing-shell section="adjustments">
        <div class="mb-4">
            <a href="{{ route('platform.revenue.adjustments') }}" class="text-sm font-semibold text-navy hover:underline">← {{ __('Adjustments') }}</a>
        </div>

        <x-platform.page-header
            :title="__('Discounts')"
            :description="__('Track discounts applied to Channel Partners.')"
        />

        <x-platform.panel compact>
            @if ($discounts->isEmpty())
                <p class="text-sm text-slate-500">{{ __('No discounts recorded yet.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead>
                            <tr class="border-b border-slate-100 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-3">{{ __('Channel Partner') }}</th>
                                <th class="px-3 py-3">{{ __('Invoice') }}</th>
                                <th class="px-3 py-3">{{ __('Discount Amount') }}</th>
                                <th class="px-3 py-3">{{ __('Reason') }}</th>
                                <th class="px-3 py-3">{{ __('Applied Date') }}</th>
                                <th class="px-3 py-3">{{ __('Applied By') }}</th>
                                <th class="px-3 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($discounts as $discount)
                                <tr class="border-b border-slate-50">
                                    <td class="px-3 py-3 text-sm font-medium text-black">{{ $discount->tenant?->name ?? '—' }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">
                                        @if ($discount->invoice)
                                            <a href="{{ route('platform.revenue.invoices.show', $discount->invoice) }}" class="hover:text-navy">#{{ $discount->invoice->number }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-sm text-black">{{ \App\Support\Platform\BillingMoney::format((int) $discount->amount) }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $discount->reason }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $discount->applied_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $discount->appliedByUser?->name ?? __('Super Admin') }}</td>
                                    <td class="px-3 py-3 text-end">
                                        @if ($discount->invoice)
                                            <x-ui.action-icon-group>
                                                <x-ui.action-icon
                                                    icon="view"
                                                    :href="route('platform.revenue.invoices.show', $discount->invoice)"
                                                    :title="__('View')"
                                                />
                                                <x-ui.action-icon
                                                    icon="invoice"
                                                    :href="route('platform.revenue.invoices.show', $discount->invoice)"
                                                    :title="__('View Invoice')"
                                                />
                                            </x-ui.action-icon-group>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $discounts->links() }}</div>
            @endif
        </x-platform.panel>
    </x-platform.billing-shell>
</x-app-layout>

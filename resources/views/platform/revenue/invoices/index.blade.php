<x-app-layout :title="__('Invoices') . ' | InSyte CRM'">
    <x-platform.billing-shell section="invoices">
        <x-platform.page-header
            :title="__('Invoices')"
            :description="__('View and manage invoices generated for Channel Partners.')"
        />

        <x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

        <form method="GET" action="{{ route('platform.revenue.invoices') }}" class="mb-4 flex flex-wrap gap-2">
            <input
                type="search"
                name="search"
                value="{{ $filters['search'] }}"
                placeholder="{{ __('Search invoice or partner...') }}"
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
            @if ($invoices->isEmpty())
                <p class="text-sm text-slate-500">{{ __('No invoices match these filters.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead>
                            <tr class="border-b border-slate-100 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-3">{{ __('Invoice #') }}</th>
                                <th class="px-3 py-3">{{ __('Channel Partner') }}</th>
                                <th class="px-3 py-3">{{ __('Amount') }}</th>
                                <th class="px-3 py-3">{{ __('Issue Date') }}</th>
                                <th class="px-3 py-3">{{ __('Due Date') }}</th>
                                <th class="px-3 py-3">{{ __('Status') }}</th>
                                <th class="px-3 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $invoice)
                                <tr class="border-b border-slate-50">
                                    <td class="px-3 py-3 text-sm font-medium text-black">
                                        <a href="{{ route('platform.revenue.invoices.show', $invoice) }}" class="hover:text-navy">#{{ $invoice->number }}</a>
                                    </td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $invoice->tenant?->name ?? $invoice->billed_to_name ?? '—' }}</td>
                                    <td class="px-3 py-3 text-sm text-black">{{ \App\Support\Platform\BillingMoney::format((int) $invoice->total) }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $invoice->issued_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $invoice->due_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</td>
                                    <td class="px-3 py-3"><x-platform.status-badge :status="$invoice->status" /></td>
                                    <td class="px-3 py-3 text-end">
                                        <x-ui.action-icon-group>
                                            <x-ui.action-icon
                                                icon="view"
                                                :href="route('platform.revenue.invoices.show', $invoice)"
                                                :title="__('View')"
                                            />
                                            <x-ui.action-icon
                                                icon="download"
                                                :href="route('platform.revenue.invoices.download', $invoice)"
                                                :title="__('Download')"
                                            />
                                            <form method="POST" action="{{ route('platform.revenue.invoices.send', $invoice) }}" class="inline">
                                                @csrf
                                                <x-ui.action-icon icon="play" type="submit" :title="__('Send')" />
                                            </form>
                                            @if ($invoice->status !== \App\Enums\BillingInvoiceStatus::Paid)
                                                <form method="POST" action="{{ route('platform.revenue.invoices.mark-paid', $invoice) }}" class="inline">
                                                    @csrf
                                                    <x-ui.action-icon icon="complete" type="submit" :title="__('Mark Paid')" />
                                                </form>
                                            @endif
                                        </x-ui.action-icon-group>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $invoices->links() }}</div>
            @endif
        </x-platform.panel>
    </x-platform.billing-shell>
</x-app-layout>

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
                <x-platform.manageable-table.wrapper :item-ids="$invoices->pluck('id')->all()">
                    <x-platform.manageable-table.bulk-bar
                        :delete-url="route('platform.revenue.invoices.bulk-destroy')"
                        :confirm-message="__('Cancel the selected unpaid invoices?')"
                    >
                        <x-slot:actions>
                            <form
                                method="POST"
                                action="{{ route('platform.revenue.invoices.bulk-mark-paid') }}"
                                class="inline"
                                @submit.prevent="if (confirm(@js(__('Mark the selected invoices as paid?')))) { $el.submit(); }"
                            >
                                @csrf
                                @foreach (request()->query() as $key => $value)
                                    @if (is_string($value) || is_numeric($value))
                                        <input type="hidden" name="_redirect_query[{{ $key }}]" value="{{ $value }}">
                                    @endif
                                @endforeach
                                <template x-for="id in selected" :key="'mark-paid-' + id">
                                    <input type="hidden" name="ids[]" :value="id">
                                </template>
                                <x-ui.button type="submit" variant="outline" size="sm">{{ __('Mark Paid') }}</x-ui.button>
                            </form>
                        </x-slot:actions>
                    </x-platform.manageable-table.bulk-bar>

                    <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead>
                            <tr class="border-b border-slate-100 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <x-platform.manageable-table.checkbox-header />
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
                                    <x-platform.manageable-table.checkbox-cell :id="$invoice->id" />
                                    <td class="px-3 py-3 text-sm font-medium text-black">
                                        <a href="{{ route('platform.revenue.invoices.show', $invoice) }}" class="hover:text-navy">#{{ $invoice->number }}</a>
                                    </td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $invoice->tenant?->name ?? $invoice->billed_to_name ?? '—' }}</td>
                                    <td class="px-3 py-3 text-sm text-black">{{ \App\Support\Platform\BillingMoney::format((int) $invoice->total) }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $invoice->issued_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $invoice->due_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</td>
                                    <td class="px-3 py-3"><x-platform.status-badge :status="$invoice->status" /></td>
                                    <td class="px-3 py-3 text-end">
                                        @include('platform.revenue.invoices.partials.action-buttons', [
                                            'invoice' => $invoice,
                                            'context' => 'list',
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </x-platform.manageable-table.wrapper>
                <div class="mt-4">{{ $invoices->links() }}</div>
            @endif
        </x-platform.panel>

        @include('platform.revenue.invoices.partials.send-dialog-list')
    </x-platform.billing-shell>
</x-app-layout>

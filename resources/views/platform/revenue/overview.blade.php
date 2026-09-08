<x-app-layout :title="__('Revenue & Billing') . ' | InSyte CRM'">
    <x-platform.billing-shell section="overview">
        <x-platform.page-header
            :title="__('Revenue & Billing')"
            :description="__('Track InSyte revenue, subscriptions, invoices, and payments.')"
        >
            <x-slot:actions>
                <form method="GET" action="{{ route('platform.revenue') }}" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="chart" value="{{ $overview['chart_window'] }}">
                    <select
                        name="range"
                        class="rounded-lg border-slate-200 text-sm text-black shadow-sm focus:border-navy focus:ring-navy"
                        onchange="this.form.submit()"
                    >
                        @foreach ([
                            'this_month' => __('This Month'),
                            'last_month' => __('Last Month'),
                            'this_quarter' => __('This Quarter'),
                            'this_year' => __('This Year'),
                            'custom' => __('Custom'),
                        ] as $value => $label)
                            <option value="{{ $value }}" @selected($overview['period']['key'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @if ($overview['period']['key'] === 'custom')
                        <input type="date" name="from" value="{{ $overview['period']['start'] }}" class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy">
                        <input type="date" name="to" value="{{ $overview['period']['end'] }}" class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy">
                        <x-ui.button type="submit" variant="outline">{{ __('Apply') }}</x-ui.button>
                    @endif
                    <x-ui.button
                        variant="outline"
                        :href="route('platform.revenue.export', request()->query())"
                    >
                        {{ __('Export') }}
                    </x-ui.button>
                </form>
            </x-slot:actions>
        </x-platform.page-header>

        <x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

        <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-platform.kpi-card
                :label="__('Total Revenue')"
                :value="$overview['snapshot']['total_revenue']"
                :change="$overview['snapshot']['comparison']"
                :change-direction="$overview['snapshot']['comparison_direction']"
                accent="emerald"
            />
            <x-platform.kpi-card
                :label="__('Collected')"
                :value="$overview['snapshot']['collected']"
                accent="sky"
            />
            <x-platform.kpi-card
                :label="__('Pending')"
                :value="$overview['snapshot']['pending']"
                accent="amber"
            />
            <x-platform.kpi-card
                :label="__('Overdue')"
                :value="$overview['snapshot']['overdue']"
                accent="rose"
            />
        </div>

        <div class="mb-6 grid gap-4 lg:grid-cols-10">
            <div class="lg:col-span-7">
                <x-platform.panel :title="__('Revenue Overview')" compact>
                    <x-slot:headerActions>
                        <div class="flex flex-wrap gap-1">
                            @foreach ([
                                '7_days' => __('7 Days'),
                                '30_days' => __('30 Days'),
                                '3_months' => __('3 Months'),
                                '12_months' => __('12 Months'),
                            ] as $value => $label)
                                <a
                                    href="{{ route('platform.revenue', array_merge(request()->except('chart'), ['chart' => $value])) }}"
                                    @class([
                                        'rounded-md px-2 py-1 text-xs font-semibold',
                                        'bg-navy text-white' => $overview['chart_window'] === $value,
                                        'text-slate-500 hover:bg-slate-50' => $overview['chart_window'] !== $value,
                                    ])
                                >{{ $label }}</a>
                            @endforeach
                        </div>
                    </x-slot:headerActions>

                    @if ($overview['chart'] === [])
                        <p class="text-sm text-slate-500">{{ __('No revenue data for this period yet.') }}</p>
                    @else
                        @include('platform.revenue.partials.revenue-chart', ['points' => $overview['chart']])
                    @endif
                </x-platform.panel>
            </div>

            <div class="lg:col-span-3">
                <x-platform.panel :title="__('By Billing Cycle')" compact>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Monthly Revenue') }}</dt>
                            <dd class="mt-1 text-xl font-bold text-black">{{ $overview['by_cycle']['monthly'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Annual Revenue') }}</dt>
                            <dd class="mt-1 text-xl font-bold text-black">{{ $overview['by_cycle']['annual'] }}</dd>
                        </div>
                    </dl>
                </x-platform.panel>
            </div>
        </div>

        <div class="mb-6 grid gap-4 lg:grid-cols-2">
            <x-platform.panel :title="__('Revenue Breakdown')" compact>
                <h3 class="mb-3 text-sm font-semibold text-black">{{ __('By Plan') }}</h3>
                @if ($overview['by_plan'] === [])
                    <p class="text-sm text-slate-500">{{ __('No plan revenue in this period.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left">
                            <thead>
                                <tr class="border-b border-slate-100 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    <th class="px-2 py-2">{{ __('Plan') }}</th>
                                    <th class="px-2 py-2">{{ __('Active Partners') }}</th>
                                    <th class="px-2 py-2">{{ __('Revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($overview['by_plan'] as $row)
                                    <tr class="border-b border-slate-50">
                                        <td class="px-2 py-2.5 text-sm font-medium text-black">{{ $row['plan'] }}</td>
                                        <td class="px-2 py-2.5 text-sm text-slate-600">{{ number_format($row['partners']) }} {{ __('partners') }}</td>
                                        <td class="px-2 py-2.5 text-sm text-black">{{ $row['revenue'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-platform.panel>

            <x-platform.panel :title="__('Needs Attention')" compact>
                @if ($overview['attention_summary'] === [])
                    <p class="text-sm text-slate-500">{{ __('Nothing needs attention right now.') }}</p>
                @else
                    <ul class="mb-4 space-y-2">
                        @foreach ($overview['attention_summary'] as $item)
                            <li class="rounded-lg border border-slate-100 px-3 py-2">
                                <p class="text-sm font-semibold text-black">{{ $item['title'] }}</p>
                                <p class="text-xs text-slate-500">{{ $item['detail'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($overview['attention_rows'] !== [])
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left">
                            <thead>
                                <tr class="border-b border-slate-100 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    <th class="px-2 py-2">{{ __('Channel Partner') }}</th>
                                    <th class="px-2 py-2">{{ __('Amount') }}</th>
                                    <th class="px-2 py-2">{{ __('Issue') }}</th>
                                    <th class="px-2 py-2">{{ __('Date') }}</th>
                                    <th class="px-2 py-2">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($overview['attention_rows'] as $row)
                                    <tr class="border-b border-slate-50 align-top">
                                        <td class="px-2 py-2.5 text-sm font-medium text-black">{{ $row['partner'] }}</td>
                                        <td class="px-2 py-2.5 text-sm text-black">{{ $row['amount'] }}</td>
                                        <td class="px-2 py-2.5 text-sm text-slate-600">{{ $row['issue'] }}</td>
                                        <td class="px-2 py-2.5 text-sm text-slate-600">{{ $row['date'] }}</td>
                                        <td class="px-2 py-2.5">
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($row['actions'] as $action)
                                                    @if (($action['method'] ?? null) === 'POST')
                                                        <form method="POST" action="{{ $action['href'] }}">
                                                            @csrf
                                                            <button type="submit" class="text-xs font-semibold text-navy hover:underline">{{ $action['label'] }}</button>
                                                        </form>
                                                    @else
                                                        <a href="{{ $action['href'] }}" class="text-xs font-semibold text-navy hover:underline">{{ $action['label'] }}</a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-platform.panel>
        </div>

        <x-platform.panel :title="__('Recent Payments')" compact>
            @if ($overview['recent_payments'] === [])
                <p class="text-sm text-slate-500">{{ __('No payments recorded yet.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead>
                            <tr class="border-b border-slate-100 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-2 py-2">{{ __('Channel Partner') }}</th>
                                <th class="px-2 py-2">{{ __('Amount') }}</th>
                                <th class="px-2 py-2">{{ __('Type') }}</th>
                                <th class="px-2 py-2">{{ __('Date') }}</th>
                                <th class="px-2 py-2">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($overview['recent_payments'] as $payment)
                                <tr class="border-b border-slate-50 hover:bg-slate-50/60">
                                    <td class="px-2 py-2.5 text-sm font-medium text-black">
                                        <a href="{{ $payment['href'] }}" class="hover:text-navy">{{ $payment['partner'] }}</a>
                                    </td>
                                    <td class="px-2 py-2.5 text-sm text-black">{{ $payment['amount'] }}</td>
                                    <td class="px-2 py-2.5 text-sm text-slate-600">{{ $payment['type'] }}</td>
                                    <td class="px-2 py-2.5 text-sm text-slate-600">{{ $payment['date'] }}</td>
                                    <td class="px-2 py-2.5"><x-platform.status-badge :status="$payment['status']" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-platform.panel>
    </x-platform.billing-shell>
</x-app-layout>

<x-app-layout :title="__('Subscriptions') . ' | InSyte CRM'">
    <x-platform.billing-shell section="subscriptions">
        <x-platform.page-header
            :title="__('Subscriptions')"
            :description="__('Manage active plans, trials, renewals, and subscription status.')"
        />

        <x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

        <form method="GET" action="{{ route('platform.revenue.subscriptions') }}" class="mb-4 flex flex-wrap gap-2">
            <input
                type="search"
                name="search"
                value="{{ $filters['search'] }}"
                placeholder="{{ __('Search Channel Partner...') }}"
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
            @if ($subscriptions->isEmpty())
                <p class="text-sm text-slate-500">{{ __('No subscriptions match these filters.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead>
                            <tr class="border-b border-slate-100 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-3">{{ __('Channel Partner') }}</th>
                                <th class="px-3 py-3">{{ __('Plan') }}</th>
                                <th class="px-3 py-3">{{ __('Billing Cycle') }}</th>
                                <th class="px-3 py-3">{{ __('Amount') }}</th>
                                <th class="px-3 py-3">{{ __('Next Billing') }}</th>
                                <th class="px-3 py-3">{{ __('Status') }}</th>
                                <th class="px-3 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subscriptions as $subscription)
                                <tr class="border-b border-slate-50">
                                    <td class="px-3 py-3 text-sm font-medium text-black">
                                        <a href="{{ route('platform.revenue.subscriptions.show', $subscription) }}" class="hover:text-navy">
                                            {{ $subscription->tenant?->name ?? __('Unknown partner') }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $subscription->plan?->name ?? '—' }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $subscription->billing_cycle?->label() ?? '—' }}</td>
                                    <td class="px-3 py-3 text-sm text-black">{{ \App\Support\Platform\BillingMoney::format((int) $subscription->amount) }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $subscription->next_billing_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</td>
                                    <td class="px-3 py-3"><x-platform.status-badge :status="$subscription->status" /></td>
                                    <td class="px-3 py-3 text-end">
                                        <x-ui.action-icon-group>
                                            <x-ui.action-icon
                                                icon="view"
                                                :href="route('platform.revenue.subscriptions.show', $subscription)"
                                                :title="__('View')"
                                            />
                                            <x-ui.action-icon
                                                icon="edit"
                                                :href="route('platform.revenue.subscriptions.show', $subscription).'#change-plan'"
                                                :title="__('Change Plan')"
                                            />
                                            <x-ui.action-icon
                                                icon="follow-up"
                                                :href="route('platform.revenue.subscriptions.show', $subscription).'#extend-trial'"
                                                :title="__('Extend Trial')"
                                            />
                                            @if ($subscription->status === \App\Enums\SubscriptionStatus::Paused)
                                                <form method="POST" action="{{ route('platform.revenue.subscriptions.resume', $subscription) }}" class="inline">
                                                    @csrf
                                                    <x-ui.action-icon icon="play" type="submit" :title="__('Resume')" />
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('platform.revenue.subscriptions.pause', $subscription) }}" class="inline">
                                                    @csrf
                                                    <x-ui.action-icon icon="cancel" type="submit" :title="__('Pause')" />
                                                </form>
                                            @endif
                                            <form
                                                method="POST"
                                                action="{{ route('platform.revenue.subscriptions.cancel', $subscription) }}"
                                                class="inline"
                                                onsubmit="return confirm(@js(__('Cancel this subscription?')));"
                                            >
                                                @csrf
                                                <x-ui.action-icon icon="delete" type="submit" :title="__('Cancel')" />
                                            </form>
                                        </x-ui.action-icon-group>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $subscriptions->links() }}</div>
            @endif
        </x-platform.panel>
    </x-platform.billing-shell>
</x-app-layout>

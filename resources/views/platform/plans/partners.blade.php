<x-app-layout :title="__('Partners') . ' · ' . $plan->name . ' | InSyte CRM'">
    <x-platform.plan-shell :plan="$plan" :shell="$shell">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-black">{{ $plan->name }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __(':count Active Partners', ['count' => number_format($partnersCount)]) }}</p>
            </div>
            <x-ui.button variant="outline" :href="route('tenants.index')">
                {{ __('View All Partners') }}
            </x-ui.button>
        </div>

        <x-platform.panel compact>
            @if ($partners->isEmpty())
                <p class="text-sm text-slate-500">{{ __('No Channel Partners are using this plan yet.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Channel Partner') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Status') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Billing') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Joined') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($partners as $partner)
                                @php
                                    $billing = $partner->getAttribute('billing_cycle');
                                    $billingLabel = match ($billing) {
                                        'monthly' => __('Monthly'),
                                        'annual' => __('Annual'),
                                        default => '—',
                                    };
                                    $status = $partner->status?->value ?? 'active';
                                    if ((int) $partner->getAttribute('trial_days') > 0 && $status === 'active') {
                                        $status = 'trial';
                                    }
                                @endphp
                                <tr>
                                    <td class="px-3 py-3 text-sm font-medium">
                                        <a href="{{ route('tenants.show', $partner) }}" class="text-navy hover:underline">{{ $partner->name }}</a>
                                    </td>
                                    <td class="px-3 py-3"><x-platform.status-badge :status="$status" /></td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $billingLabel }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $partner->created_at?->timezone(config('app.timezone'))->format('d M') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-platform.panel>
    </x-platform.plan-shell>
</x-app-layout>

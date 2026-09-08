<x-app-layout :title="__('Dashboard') . ' | InSyte CRM'">
    @php
        $maxIntegrationCount = max(collect($dashboard['usage']['integrations'])->max('count') ?? 0, 1);
    @endphp

    <div class="mb-5">
        <h1 class="text-2xl font-bold tracking-tight text-black">
            {{ $greeting }}, {{ $userName }}
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            {{ __('Here\'s what\'s happening across InSyte today.') }}
        </p>
        <p class="mt-0.5 text-xs text-slate-400">{{ $contextDate }}</p>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ($dashboard['kpis'] as $kpi)
            <x-platform.kpi-card
                :label="$kpi['label']"
                :value="$kpi['value']"
                :change="$kpi['change']"
                :change-direction="$kpi['change_direction']"
                :href="$kpi['href']"
                :accent="match ($kpi['key']) {
                    'revenue' => 'emerald',
                    'trials' => 'amber',
                    'users' => 'sky',
                    default => 'navy',
                }"
            >
                <x-slot:icon>
                    @switch($kpi['key'])
                        @case('partners')
                            <x-sidebar.nav-icon name="partners" />
                            @break
                        @case('revenue')
                            <x-sidebar.nav-icon name="revenue" />
                            @break
                        @case('trials')
                            <x-sidebar.nav-icon name="plans" />
                            @break
                        @default
                            <x-sidebar.nav-icon name="teams" />
                    @endswitch
                </x-slot:icon>
            </x-platform.kpi-card>
        @endforeach
    </div>

    <div class="mb-6 grid gap-4 lg:grid-cols-10 lg:items-stretch">
        <div class="min-w-0 lg:col-span-6">
            <x-platform.panel class="h-full" :title="__('Revenue')" compact>
                <x-slot:headerActions>
                    <a href="{{ $dashboard['revenue']['href'] }}" class="text-xs font-semibold text-navy hover:underline">
                        {{ __('View Revenue & Billing') }} →
                    </a>
                </x-slot:headerActions>

                <div class="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-start">
                    <div>
                        @include('platform.dashboard.partials.revenue-chart', ['points' => $dashboard['revenue']['months']])
                    </div>
                    <dl class="grid grid-cols-3 gap-3 sm:w-40 sm:grid-cols-1">
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">{{ __('MRR') }}</dt>
                            <dd class="mt-0.5 text-sm font-bold text-black">{{ $dashboard['revenue']['mrr'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">{{ __('ARR') }}</dt>
                            <dd class="mt-0.5 text-sm font-bold text-black">{{ $dashboard['revenue']['arr'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">{{ __('ARPU') }}</dt>
                            <dd class="mt-0.5 text-sm font-bold text-black">{{ $dashboard['revenue']['arpu'] }}</dd>
                        </div>
                    </dl>
                </div>
            </x-platform.panel>
        </div>

        <div class="min-w-0 lg:col-span-4">
            <x-platform.panel class="h-full" :title="__('Needs Attention')" compact>
            @if ($dashboard['attention'] === [])
                <p class="text-sm text-slate-500">{{ __('All clear across InSyte.') }}</p>
            @else
                <ul class="space-y-3">
                    @foreach ($dashboard['attention'] as $item)
                        @php
                            $severityClass = match ($item['severity']) {
                                'critical' => 'bg-rose-500',
                                'warning' => 'bg-amber-500',
                                default => 'bg-sky-500',
                            };
                        @endphp
                        <li>
                            <a href="{{ $item['href'] }}" class="group block rounded-xl border border-slate-100 px-3 py-2.5 transition-colors hover:border-slate-200 hover:bg-slate-50/70">
                                <div class="flex items-start gap-2.5">
                                    <span class="mt-1.5 size-2 shrink-0 rounded-full {{ $severityClass }}" aria-hidden="true"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-black">{{ $item['title'] }}</p>
                                        <p class="mt-0.5 text-sm text-slate-600">{{ $item['summary'] }}</p>
                                        @if ($item['meta'])
                                            <p class="mt-0.5 text-xs font-medium text-slate-500">{{ $item['meta'] }}</p>
                                        @endif
                                        <p class="mt-1.5 text-xs font-semibold text-navy group-hover:underline">
                                            {{ $item['action_label'] }} →
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
            </x-platform.panel>
        </div>
    </div>

    <div class="mb-6 grid gap-4 lg:grid-cols-2">
        <x-platform.panel :title="__('Channel Partner Overview')" compact>
            <x-slot:headerActions>
                <a href="{{ $dashboard['partners']['href'] }}" class="text-xs font-semibold text-navy hover:underline">
                    {{ __('View All Channel Partners') }} →
                </a>
            </x-slot:headerActions>

            <p class="mb-4 text-3xl font-bold tracking-tight text-black">
                {{ number_format($dashboard['partners']['total']) }}
                <span class="text-sm font-medium text-slate-500">{{ __('Total') }}</span>
            </p>

            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                @foreach ($dashboard['partners']['statuses'] as $status)
                    <a
                        href="{{ $status['href'] }}"
                        class="rounded-xl border border-slate-100 px-3 py-2.5 transition-colors hover:border-slate-200 hover:bg-slate-50/70"
                    >
                        <p class="text-xs font-medium text-slate-500">{{ $status['label'] }}</p>
                        <p class="mt-1 text-lg font-bold text-black">{{ number_format($status['count']) }}</p>
                    </a>
                @endforeach
            </div>
        </x-platform.panel>

        <x-platform.panel :title="__('Platform Usage')" compact>
            <x-slot:headerActions>
                <a href="{{ $dashboard['usage']['href'] }}" class="text-xs font-semibold text-navy hover:underline">
                    {{ __('View Integrations') }} →
                </a>
            </x-slot:headerActions>

            <div class="mb-4">
                <p class="text-xs font-medium text-slate-500">{{ __('Total Active Integrations') }}</p>
                <p class="mt-0.5 text-3xl font-bold tracking-tight text-black">{{ number_format($dashboard['usage']['total_active']) }}</p>
                <p @class([
                    'mt-1 text-xs font-medium',
                    'text-emerald-600' => $dashboard['usage']['change_direction'] === 'up',
                    'text-rose-600' => $dashboard['usage']['change_direction'] === 'down',
                    'text-slate-500' => $dashboard['usage']['change_direction'] === 'flat',
                ])>{{ $dashboard['usage']['change'] }}</p>
            </div>

            <ul class="space-y-2.5">
                @foreach ($dashboard['usage']['integrations'] as $integration)
                    <li>
                        <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                            <span class="font-medium text-slate-700">{{ $integration['name'] }}</span>
                            <span class="tabular-nums font-semibold text-black">{{ number_format($integration['count']) }}</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full bg-navy/80"
                                style="width: {{ max(8, ($integration['count'] / $maxIntegrationCount) * 100) }}%"
                            ></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-platform.panel>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <x-platform.panel :title="__('Recent Activity')" compact>
            <x-slot:headerActions>
                <a href="{{ route('platform.analytics') }}" class="text-xs font-semibold text-navy hover:underline">
                    {{ __('View Activity') }} →
                </a>
            </x-slot:headerActions>

            <ul class="divide-y divide-slate-100">
                @foreach ($dashboard['activity'] as $event)
                    <li class="py-2.5 first:pt-0 last:pb-0">
                        @if ($event['href'])
                            <a href="{{ $event['href'] }}" class="flex gap-3 transition-colors hover:text-navy">
                                <span class="w-16 shrink-0 text-xs font-medium tabular-nums text-slate-400">{{ $event['time'] }}</span>
                                <span class="min-w-0 text-sm text-slate-700">{{ $event['description'] }}</span>
                            </a>
                        @else
                            <div class="flex gap-3">
                                <span class="w-16 shrink-0 text-xs font-medium tabular-nums text-slate-400">{{ $event['time'] }}</span>
                                <span class="min-w-0 text-sm text-slate-700">{{ $event['description'] }}</span>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </x-platform.panel>

        <x-platform.panel :title="__('Upcoming')" compact>
            <div class="space-y-4">
                <div>
                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Today') }}</h3>
                    <ul class="space-y-1.5">
                        @foreach ($dashboard['upcoming']['today'] as $item)
                            <li>
                                <a href="{{ $item['href'] }}" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-navy">
                                    <span class="size-1.5 shrink-0 rounded-full bg-navy" aria-hidden="true"></span>
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('This Week') }}</h3>
                    <ul class="space-y-1.5">
                        @foreach ($dashboard['upcoming']['this_week'] as $item)
                            <li>
                                <a href="{{ $item['href'] }}" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-navy">
                                    <span class="size-1.5 shrink-0 rounded-full bg-slate-300" aria-hidden="true"></span>
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </x-platform.panel>
    </div>
</x-app-layout>

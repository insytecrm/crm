@props([
    'pipeline',
    'periodFilter',
])

@php
    use App\Enums\DashboardPeriod;

    $period = $periodFilter->period;
    $maxCount = max((int) $pipeline['max_count'], 1);
    $tickStep = max(1, (int) ceil($maxCount / 5));
    $scaleMax = (int) (ceil($maxCount / $tickStep) * $tickStep);
    if ($scaleMax < $maxCount) {
        $scaleMax = $maxCount;
    }
    $ticks = range(0, $scaleMax, $tickStep);

    $periodOptions = collect(DashboardPeriod::cases())
        ->map(fn (DashboardPeriod $option): array => [
            'value' => $option->value,
            'label' => $option->label(),
            'is_custom' => $option === DashboardPeriod::Custom,
        ])
        ->values()
        ->all();
@endphp

<section
    x-data="pipelineChart(@js([
        'endpoint' => route('tenant.dashboard.pipeline'),
        'period' => $period->value,
        'periodLabel' => $period->label(),
        'from' => $periodFilter->from,
        'to' => $periodFilter->to,
        'stages' => $pipeline['stages'],
        'scaleMax' => $scaleMax,
        'ticks' => $ticks,
        'periodOptions' => $periodOptions,
        'customValue' => DashboardPeriod::Custom->value,
    ]))"
    class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm"
    style="height: 20rem"
>
    <div class="relative flex shrink-0 items-center justify-between gap-3 border-b border-slate-100 px-3 py-2 sm:px-4">
        <div class="min-w-0">
            <h2 class="text-sm font-semibold leading-none text-black">{{ __('Sales Pipeline') }}</h2>
            <p class="mt-0.5 text-[11px] leading-none text-slate-500">{{ __('Leads by status') }}</p>
        </div>

        <div class="relative shrink-0">
            <button
                type="button"
                @click="open = !open; customOpen = false"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50"
                :aria-expanded="open"
            >
                <span x-text="periodLabel"></span>
                <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <div
                x-show="open"
                x-cloak
                @click.outside="open = false"
                x-transition
                class="absolute end-0 z-30 mt-1.5 w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg"
                style="display: none;"
            >
                <template x-for="option in periodOptions" :key="option.value">
                    <button
                        type="button"
                        @click="selectPeriod(option)"
                        class="block w-full px-3 py-1.5 text-start text-xs transition"
                        :class="period === option.value ? 'bg-slate-50 font-semibold text-navy' : 'text-slate-700 hover:bg-slate-50'"
                        x-text="option.label"
                    ></button>
                </template>
            </div>

            <div
                x-show="customOpen"
                x-cloak
                @click.outside="customOpen = false"
                x-transition
                class="absolute end-0 z-30 mt-1.5 w-64 rounded-xl border border-slate-200 bg-white p-3 shadow-lg"
                style="display: none;"
            >
                <form @submit.prevent="applyCustomRange()" class="space-y-2.5">
                    <div>
                        <label for="pipeline_from" class="mb-1 block text-[11px] font-semibold text-slate-600">{{ __('From') }}</label>
                        <input
                            id="pipeline_from"
                            type="date"
                            x-model="from"
                            class="block h-8 w-full rounded-lg border border-slate-200 bg-white px-2 text-xs text-black shadow-sm focus:border-navy focus:ring-navy"
                            required
                        >
                    </div>
                    <div>
                        <label for="pipeline_to" class="mb-1 block text-[11px] font-semibold text-slate-600">{{ __('To') }}</label>
                        <input
                            id="pipeline_to"
                            type="date"
                            x-model="to"
                            class="block h-8 w-full rounded-lg border border-slate-200 bg-white px-2 text-xs text-black shadow-sm focus:border-navy focus:ring-navy"
                            required
                        >
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="customOpen = false" class="rounded-lg px-2 py-1 text-[11px] font-semibold text-slate-600 hover:bg-slate-50">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="rounded-lg bg-navy px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-navy/90" :disabled="loading">
                            {{ __('Apply') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="relative flex min-h-0 flex-1 flex-col p-2">
        <div
            x-show="loading"
            x-cloak
            class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/50"
        >
            <span class="text-[11px] font-medium text-slate-500">{{ __('Updating…') }}</span>
        </div>

        <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-lg bg-slate-100/80 px-2 py-2">
            <div class="relative min-h-0 flex-1">
                <div class="pointer-events-none absolute inset-y-0 end-0" style="left: 5.75rem">
                    <template x-for="tick in ticks" :key="'g'+tick">
                        <div
                            x-show="tick > 0"
                            class="absolute inset-y-0 border-s border-white/90"
                            :style="'left:' + ((tick / scaleMax) * 100) + '%'"
                        ></div>
                    </template>
                </div>

                <div class="relative flex h-full flex-col justify-between gap-1">
                    <template x-for="stage in stages" :key="stage.status">
                        <a
                            :href="stage.href"
                            class="group grid items-center gap-2"
                            style="grid-template-columns: 5.75rem 1fr"
                            :title="stage.count + ' leads · ' + stage.percentage + '% of total'"
                        >
                            <span class="truncate text-start text-xs font-medium text-slate-600" x-text="stage.label"></span>

                            <div class="relative flex h-4 items-center">
                                <div
                                    class="h-full rounded-sm shadow-sm transition-all duration-300"
                                    :style="'width:' + barWidth(stage.count) + '%;' + stage.bar_style"
                                ></div>

                                <span
                                    class="pointer-events-none absolute -top-7 start-1/2 z-10 hidden -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-[11px] font-medium text-white shadow-sm group-hover:block"
                                    x-text="Number(stage.count).toLocaleString() + ' · ' + stage.percentage + '%'"
                                ></span>
                            </div>
                        </a>
                    </template>
                </div>
            </div>

            <div class="mt-1.5 grid gap-2" style="grid-template-columns: 5.75rem 1fr">
                <div></div>
                <div class="relative h-3.5">
                    <template x-for="tick in ticks" :key="'t'+tick">
                        <span
                            class="absolute top-0 text-[10px] tabular-nums text-slate-500"
                            :class="{
                                '-translate-x-1/2': tick > 0 && tick < scaleMax,
                                'translate-x-0': tick === 0,
                                '-translate-x-full': tick === scaleMax,
                            }"
                            :style="'left:' + ((tick / scaleMax) * 100) + '%'"
                            x-text="Number(tick).toLocaleString()"
                        ></span>
                    </template>
                </div>
            </div>
        </div>
    </div>
</section>

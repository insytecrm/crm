@props([
    'items',
    'counts',
])

<section
    x-data="{
        kind: 'all',
        setKind(value) {
            this.kind = this.kind === value ? 'all' : value;
        },
        matches(itemKind) {
            return this.kind === 'all' || this.kind === itemKind;
        },
    }"
    class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm shadow-slate-200/40"
>
    <div class="flex shrink-0 flex-wrap items-center gap-2 bg-navy-dark px-3 py-2.5 sm:gap-3 sm:px-4">
        <div class="flex min-w-0 items-center gap-2.5">
            <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white/20 text-white">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>
            <div class="min-w-0 shrink-0">
                <h2 class="text-sm font-bold leading-none text-white">{{ __('Needs Attention') }}</h2>
                <p class="mt-0.5 text-[11px] leading-none text-white/75">
                    {{ trans_choice(':count item needs attention|:count items need attention', $items->count(), ['count' => $items->count()]) }}
                </p>
            </div>
        </div>

        <div class="flex min-w-0 flex-1 flex-wrap items-center justify-end gap-1.5">
            @foreach ([
                ['key' => 'trial', 'label' => __('Trials'), 'count' => $counts['trials']],
                ['key' => 'subscription', 'label' => __('Renewals'), 'count' => $counts['subscriptions']],
                ['key' => 'quotation', 'label' => __('Drafts'), 'count' => $counts['draft_quotations']],
                ['key' => 'invoice', 'label' => __('Unpaid'), 'count' => $counts['unpaid_invoices']],
            ] as $filter)
                <button
                    type="button"
                    @click="setKind(@js($filter['key']))"
                    :aria-pressed="kind === @js($filter['key'])"
                    :class="kind === @js($filter['key'])
                        ? 'border-white/40 bg-white/20 ring-1 ring-white/30'
                        : 'border-white/15 bg-white/5 hover:bg-white/10'"
                    class="inline-flex h-8 max-w-full items-center gap-1.5 rounded-lg border px-2 transition"
                >
                    <span class="truncate text-[10px] font-medium text-white/80">{{ $filter['label'] }}</span>
                    <span class="text-sm font-bold tabular-nums leading-none text-white">{{ number_format($filter['count']) }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <div class="min-h-0 flex-1 overflow-y-auto p-3 sm:p-4" style="max-height: 22rem;">
        @if ($items->isEmpty())
            <p class="text-sm text-slate-500">{{ __('All clear across InSyte.') }}</p>
        @else
            <ul class="space-y-2">
                @foreach ($items as $item)
                    <li x-show="matches(@js($item['kind']))" x-cloak>
                        @php
                            $severityClass = match ($item['severity']) {
                                'critical' => 'bg-rose-500',
                                'warning' => 'bg-amber-500',
                                default => 'bg-sky-500',
                            };
                        @endphp
                        <a href="{{ $item['href'] }}" class="group block rounded-xl border border-slate-100 px-3 py-2.5 transition-colors hover:border-slate-200 hover:bg-slate-50/70">
                            <div class="flex items-start gap-2.5">
                                <span class="mt-1.5 size-2 shrink-0 rounded-full {{ $severityClass }}" aria-hidden="true"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-black">{{ $item['label'] }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $item['meta'] }}</p>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>

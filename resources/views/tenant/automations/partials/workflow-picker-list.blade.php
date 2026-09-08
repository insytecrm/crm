@props([
    'sections' => [],
    'select',
])

<div class="flex flex-col gap-5 px-4 py-4">
    @foreach ($sections as $section)
        <section class="flex flex-col gap-2">
            <h3 class="px-0.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $section['label'] }}</h3>
            <ul class="overflow-hidden rounded-xl border border-slate-100 bg-white">
                @foreach ($section['items'] as $item)
                    <li class="border-b border-slate-100 last:border-b-0">
                        <button
                            type="button"
                            @class([
                                'flex w-full items-start gap-3 px-3 py-2.5 text-left transition-colors',
                                'hover:bg-slate-50' => $item['selectable'],
                                'cursor-not-allowed bg-slate-50/60 opacity-60' => ! $item['selectable'],
                            ])
                            @if ($item['selectable'])
                                @click="{{ $select }}('{{ $item['value'] }}')"
                            @else
                                disabled
                            @endif
                        >
                            <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-navy/10 text-navy">
                                <svg class="size-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M11.25 2.25 4.5 13.5h6.75L9.75 21.75l9-12.75h-6.75L15.75 2.25h-4.5Z" />
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-black">{{ $item['label'] }}</span>
                                    @if ($item['comingSoon'])
                                        <span class="rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Coming soon') }}</span>
                                    @endif
                                </span>
                                <span class="mt-0.5 block text-xs leading-4 text-slate-500">{{ $item['description'] }}</span>
                            </span>
                            @if ($item['selectable'])
                                <svg class="mt-1 size-4 shrink-0 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            @endif
                        </button>
                    </li>
                @endforeach
            </ul>
        </section>
    @endforeach
</div>

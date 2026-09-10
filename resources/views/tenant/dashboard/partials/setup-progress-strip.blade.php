@props([
    'setupProgress',
])

@if ($setupProgress['completed'] < $setupProgress['total'] && ! $setupProgress['dismissed'])
    <div
        x-data="{ open: false }"
        class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
    >
        <button
            type="button"
            class="flex w-full items-center gap-3 px-4 py-2.5 text-start transition hover:bg-slate-50"
            @click="open = !open"
            :aria-expanded="open"
        >
            <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg bg-navy/10 text-xs font-bold text-navy">
                {{ $setupProgress['completed'] }}/{{ $setupProgress['total'] }}
            </span>
            <span class="min-w-0 flex-1 text-sm font-medium text-black">{{ __('Setup') }}</span>
            <svg
                class="size-4 shrink-0 text-slate-400 transition-transform"
                :class="open && 'rotate-180'"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>

        <div x-show="open" x-cloak class="border-t border-slate-100 px-4 py-3">
            <ul class="space-y-2">
                @foreach ($setupProgress['items'] as $item)
                    <li class="flex items-center gap-2 text-sm">
                        @if ($item['done'])
                            <span class="inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700" aria-hidden="true">✓</span>
                            <span class="text-slate-500 line-through">{{ $item['label'] }}</span>
                        @elseif ($item['href'])
                            <span class="inline-flex size-5 shrink-0 items-center justify-center rounded-full border border-slate-200 text-[10px] text-slate-400" aria-hidden="true">○</span>
                            <a href="{{ $item['href'] }}" class="font-medium text-navy hover:underline">{{ $item['label'] }}</a>
                        @else
                            <span class="inline-flex size-5 shrink-0 items-center justify-center rounded-full border border-slate-200 text-[10px] text-slate-400" aria-hidden="true">○</span>
                            <span class="text-slate-600">{{ $item['label'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

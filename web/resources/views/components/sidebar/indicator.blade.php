@props([
    'count' => null,
])

<span {{ $attributes->merge(['class' => 'ms-auto inline-flex shrink-0 items-center justify-center']) }}>
    @if ($count !== null && (int) $count > 0)
        <span class="inline-flex min-h-4 min-w-4 items-center justify-center rounded-full bg-amber-500 px-1 text-[10px] font-bold leading-none text-white">
            {{ (int) $count > 9 ? '9+' : (int) $count }}
        </span>
    @else
        <span class="size-2 rounded-full bg-amber-500" aria-hidden="true"></span>
    @endif
</span>

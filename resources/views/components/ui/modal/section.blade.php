@props([
    'title',
])

<div {{ $attributes->merge(['class' => '']) }}>
    <div class="mb-3 flex items-center gap-2 border-b border-slate-100 pb-2">
        <span class="flex size-5 shrink-0 items-center justify-center rounded-full bg-navy/10 text-black">
            <svg class="size-3" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="4" />
            </svg>
        </span>
        <h3 class="text-xs font-semibold uppercase tracking-wide text-black">{{ $title }}</h3>
    </div>
    {{ $slot }}
</div>

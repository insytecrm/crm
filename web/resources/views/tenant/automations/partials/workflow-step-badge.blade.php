@props([
    'label',
])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600']) }}>
    <svg class="size-2.5 text-navy" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M11.25 2.25 4.5 13.5h6.75L9.75 21.75l9-12.75h-6.75L15.75 2.25h-4.5Z" />
    </svg>
    {{ $label }}
</span>

@props([
    'title',
    'compact' => false,
])

<div {{ $attributes->merge(['class' => $compact
    ? 'rounded-xl border border-slate-100 bg-white p-4 shadow-sm'
    : 'rounded-2xl border border-slate-100 bg-white p-6 shadow-sm']) }}>
    <div @class([
        'flex items-center gap-2.5 border-b border-slate-100',
        'mb-3 pb-2.5' => $compact,
        'mb-5 gap-3 pb-4' => ! $compact,
    ])>
        @isset($icon)
            <div @class([
                'flex shrink-0 items-center justify-center rounded-lg bg-navy/5 text-black',
                'size-8' => $compact,
                'size-10 rounded-xl' => ! $compact,
            ])>
                {{ $icon }}
            </div>
        @endisset
        <h2 @class([
            'font-semibold text-black',
            'text-base' => $compact,
            'text-lg' => ! $compact,
        ])>{{ $title }}</h2>
    </div>

    {{ $slot }}
</div>

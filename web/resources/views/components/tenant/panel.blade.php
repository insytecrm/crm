@props([
    'title' => null,
    'description' => null,
    'compact' => false,
])

@php
    $hasTitleHeader = filled($title);
@endphp

@if ($hasTitleHeader)
    <div {{ $attributes->class([
        'flex flex-col overflow-hidden border border-slate-100 bg-white shadow-sm shadow-slate-200/40',
        'rounded-xl' => $compact,
        'rounded-2xl' => ! $compact,
    ]) }}>
        <div @class([
            'flex items-center justify-between gap-3 bg-navy-dark',
            'px-4 py-3' => $compact,
            'px-5 py-3.5' => ! $compact,
        ])>
            <div class="flex min-w-0 items-center gap-2.5">
                <div @class([
                    'flex shrink-0 items-center justify-center rounded-lg bg-white/20 text-white [&_svg]:size-4 [&_svg]:text-white',
                    'size-8' => $compact,
                    'size-9' => ! $compact,
                ])>
                    @isset($icon)
                        {{ $icon }}
                    @else
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    @endisset
                </div>
                <div class="min-w-0">
                    <h2 @class([
                        'font-bold leading-tight text-white',
                        'text-sm' => $compact,
                        'text-base' => ! $compact,
                    ])>{{ $title }}</h2>
                    @if ($description)
                        <p class="mt-0.5 text-xs leading-snug text-white/80">{{ $description }}</p>
                    @endif
                </div>
            </div>

            @isset($headerActions)
                <div class="flex shrink-0 flex-wrap items-center justify-end gap-1.5 [&_a]:!text-white/90 hover:[&_a]:!bg-white/15 hover:[&_a]:!text-white [&_a.bg-navy]:!bg-white/25 [&_a.bg-navy]:!text-white [&_a.text-slate-500]:!text-white/75">
                    {{ $headerActions }}
                </div>
            @endisset
        </div>

        <div @class([
            'min-h-0 flex-1',
            'p-4' => $compact,
            'p-6' => ! $compact,
        ])>
            {{ $slot }}
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' => $compact
        ? 'rounded-xl border border-slate-100 bg-white p-4 shadow-sm'
        : 'rounded-2xl border border-slate-100 bg-white p-6 shadow-sm']) }}>
        @if (isset($icon) || isset($headerActions))
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

                <div class="min-w-0 flex-1"></div>

                @isset($headerActions)
                    <div class="flex shrink-0 items-center gap-2">
                        {{ $headerActions }}
                    </div>
                @endisset
            </div>
        @endif

        {{ $slot }}
    </div>
@endif

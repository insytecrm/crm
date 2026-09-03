@props([])

<div {{ $attributes->merge(['class' => 'mb-4 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm']) }}>
    <div
        @class([
            'flex flex-col gap-3 p-3 sm:flex-row sm:items-center sm:p-4',
            'sm:justify-between' => isset($search),
            'sm:justify-end' => ! isset($search),
        ])
    >
        @isset($search)
            <div class="w-full sm:max-w-md">
                {{ $search }}
            </div>
        @endisset

        <div class="flex flex-wrap items-center gap-2">
            {{ $slot }}
        </div>
    </div>

    {{ $panel ?? '' }}
</div>

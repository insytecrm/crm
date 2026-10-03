@props([
    'modalName',
])

<button
    type="button"
    {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-100 px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy focus-visible:ring-offset-2']) }}
    @click="$dispatch('close-modal', @js($modalName))"
>
    <span class="flex size-4.5 items-center justify-center rounded-full bg-slate-300/80 text-slate-600">
        <svg class="size-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
    </span>
    {{ $slot->isEmpty() ? __('Cancel') : $slot }}
</button>

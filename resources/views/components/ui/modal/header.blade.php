@props([
    'title',
    'description' => null,
    'modalName' => null,
])

<div class="rounded-t-2xl bg-navy px-5 py-3.5">
    <div class="flex items-center justify-between gap-3">
        <div class="flex min-w-0 items-center gap-2.5">
            @if (isset($icon))
                <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white">
                    {{ $icon }}
                </div>
            @endif
            <div class="min-w-0">
                <h2 class="text-base font-bold leading-tight text-white">{{ $title }}</h2>
                @if ($description)
                    <p class="mt-0.5 text-xs leading-snug text-white/75">{{ $description }}</p>
                @endif
            </div>
        </div>
        @if ($modalName)
            <button
                type="button"
                class="shrink-0 rounded-md p-1 text-white/80 transition hover:bg-white/10 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/50"
                @click="$dispatch('close-modal', @js($modalName))"
                aria-label="{{ __('Close') }}"
            >
                <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </div>
</div>

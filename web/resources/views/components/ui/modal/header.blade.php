@props([
    'title',
    'description' => null,
    'modalName' => null,
])

<div class="rounded-t-2xl bg-navy-dark px-5 py-3.5">
    <div class="flex items-center justify-between gap-3">
        <div class="flex min-w-0 items-center gap-2.5">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-white/20 text-white [&_svg]:size-4 [&_svg]:text-white">
                @isset($icon)
                    {{ $icon }}
                @else
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                @endisset
            </div>
            <div class="min-w-0">
                <h2 class="text-base font-bold leading-tight text-white">{{ $title }}</h2>
                @if ($description)
                    <p class="mt-0.5 text-xs leading-snug text-white/80">{{ $description }}</p>
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
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </div>
</div>

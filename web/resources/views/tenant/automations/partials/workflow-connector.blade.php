@props([
    'action' => 'addAction',
])

<div class="relative flex h-7 items-center justify-center">
    <div class="absolute inset-y-0 left-1/2 w-px -translate-x-1/2 bg-navy/40"></div>
    <div class="relative z-[1]" x-data="{ open: false }">
        <button
            type="button"
            class="flex size-5 items-center justify-center rounded-full bg-navy text-white shadow-sm hover:bg-navy/90"
            @click="open = ! open"
            :aria-expanded="open"
            aria-label="{{ __('Add step') }}"
        >
            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5" />
            </svg>
        </button>
        <div
            x-show="open"
            x-cloak
            @click.outside="open = false"
            class="absolute left-1/2 z-20 mt-1.5 w-36 -translate-x-1/2 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-sm"
        >
            @if ($action === 'addFilterOrAction')
                <button
                    type="button"
                    class="block w-full px-2.5 py-1.5 text-left text-xs text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400"
                    @click="addFilter(); open = false"
                    :disabled="conditions.length > 0"
                >
                    {{ __('Add filter') }}
                </button>
            @endif
            <button
                type="button"
                class="block w-full px-2.5 py-1.5 text-left text-xs text-slate-700 hover:bg-slate-50"
                @click.stop="openActionPicker(); open = false"
            >
                {{ __('Add action') }}
            </button>
        </div>
    </div>
</div>

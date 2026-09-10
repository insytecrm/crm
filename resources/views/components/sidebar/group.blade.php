<div x-data="{ open: @js($active) }" class="sidebar-group">
    <div
        @class([
            'flex w-full items-center rounded-md transition-colors',
            'bg-sidebar-accent text-sidebar-accent-foreground' => $active,
        ])
    >
        <a
            href="{{ $href }}"
            @click="$dispatch('close-drawer', 'mobile-nav')"
            @class([
                'sidebar-link group flex min-w-0 flex-1 items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                'text-sidebar-accent-foreground' => $active,
                'text-sidebar-foreground/80 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground' => ! $active,
            ])
        >
            <span class="shrink-0">
                {{ $icon }}
            </span>
            <span class="sidebar-label min-w-0 flex-1 truncate">{{ $slot }}</span>
            @if ($indicator)
                <x-sidebar.indicator :count="$indicator" class="sidebar-label me-1" />
            @endif
        </a>

        <button
            type="button"
            @click="open = !open"
            class="sidebar-label shrink-0 rounded-md px-2 py-2 text-sidebar-foreground/60 transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
            :aria-expanded="open"
            aria-label="{{ __('Toggle submenu') }}"
        >
            <svg
                class="h-4 w-4 transition-transform"
                :class="open && 'rotate-180'"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.8"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>
    </div>

    <div x-show="open" x-cloak class="sidebar-group-items space-y-1">
        {{ $submenu }}
    </div>
</div>

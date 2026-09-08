<a
    href="{{ $href }}"
    @click="$dispatch('close-drawer', 'mobile-nav')"
    @class([
        'sidebar-link group flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
        'bg-sidebar-accent text-sidebar-accent-foreground' => $active,
        'text-sidebar-foreground/80 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground' => ! $active,
    ])
>
    <span class="flex h-5 w-5 shrink-0 items-center justify-center">
        @isset($icon)
            {{ $icon }}
        @else
            <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
        @endisset
    </span>
    <span class="sidebar-label truncate">{{ $slot }}</span>
</a>

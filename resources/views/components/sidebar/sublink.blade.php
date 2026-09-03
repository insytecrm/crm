<a
    href="{{ $href }}"
    @click="$dispatch('close-drawer', 'mobile-nav')"
    @class([
        'sidebar-link group flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
        'bg-sidebar-accent text-sidebar-accent-foreground' => $active,
        'text-sidebar-foreground/80 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground' => ! $active,
    ])
>
    <span class="flex h-4 w-4 shrink-0 items-center justify-center {{ $active ? '' : 'text-sidebar-foreground/50 group-hover:text-sidebar-accent-foreground' }}">
        <span class="h-1 w-1 rounded-full bg-current" aria-hidden="true"></span>
    </span>
    <span class="sidebar-label truncate">{{ $slot }}</span>
</a>

<a
    href="{{ $href }}"
    @click="$dispatch('close-drawer', 'mobile-nav')"
    @class([
        'sidebar-link group flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
        'bg-sidebar-accent text-sidebar-accent-foreground' => $active,
        'text-sidebar-foreground/80 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground' => ! $active,
    ])
>
    <span class="shrink-0 {{ $active ? '' : 'text-sidebar-foreground/70 group-hover:text-sidebar-accent-foreground' }}">
        {{ $icon }}
    </span>
    <span class="sidebar-label truncate">{{ $slot }}</span>
</a>

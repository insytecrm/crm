<a
    href="{{ $href }}"
    @click="$dispatch('close-drawer', 'mobile-nav')"
    @class([
        'sidebar-link group flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
        'bg-sidebar-accent text-sidebar-accent-foreground' => $active,
        'text-sidebar-foreground/80 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground' => ! $active,
    ])
>
    <span class="shrink-0">
        {{ $icon }}
    </span>
    <span class="sidebar-label min-w-0 flex-1 truncate">{{ $slot }}</span>
    @if ($indicator)
        <x-sidebar.indicator :count="$indicator" class="sidebar-label" />
    @endif
</a>

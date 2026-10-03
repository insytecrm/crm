@php($drawerConfig = $config())

<div
    x-data="uiDrawer(@js($drawerConfig))"
    x-on:open-drawer.window="handleOpenEvent($event)"
    x-on:close-drawer.window="handleCloseEvent($event)"
    @if ($closeOnEscape)
        @keydown.escape.window="if (visible) closeDrawer()"
    @endif
>
    @if ($teleport)
        <template x-teleport="body">
            @include('components.ui.drawer-panel')
        </template>
    @else
        @include('components.ui.drawer-panel')
    @endif
</div>

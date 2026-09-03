<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="app-density-compact">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'InSyte CRM') }}</title>

        <x-favicon />

        <x-fonts />

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="font-sans antialiased text-black @stack('body-class')"
        x-data="{ collapsed: false }"
        :style="collapsed ? '--sidebar-width: 72px' : '--sidebar-width: 256px'"
    >
        <div
            class="sidebar-app flex h-dvh overflow-hidden"
            :class="{ 'sidebar-collapsed': collapsed }"
        >
            <div id="mobile-nav-drawer-root" class="lg:hidden"></div>

            {{-- Sidebar --}}
            <aside
                :class="[
                    $store.drawers.isOpen('mobile-nav') ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                ]"
                class="sidebar-panel fixed inset-y-0 left-0 z-50 flex h-dvh flex-col border-r border-sidebar-border bg-sidebar text-sidebar-foreground transition-all duration-300 ease-out lg:static lg:h-full lg:shrink-0 lg:transition-[width]"
            >
                <div
                    class="flex h-[56px] items-center gap-2 overflow-hidden border-b border-sidebar-border px-4"
                    :class="collapsed && 'lg:justify-center lg:px-2'"
                >
                    <x-auth.brand variant="light" />
                    @if ($contextBadge)
                        <span class="sidebar-label rounded-full bg-sidebar-primary px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-sidebar-primary-foreground">{{ $contextBadge }}</span>
                    @endif
                </div>

                @if ($contextLabel)
                    <div class="sidebar-label border-b border-sidebar-border px-4 py-3 text-xs font-medium uppercase tracking-wide text-sidebar-foreground/60">
                        {{ $contextLabel }}
                    </div>
                @endif

                <x-ui.scroll-area class="min-h-0 flex-1">
                    <nav class="space-y-1 p-3">
                        {{ $navigation }}
                    </nav>
                </x-ui.scroll-area>

                @isset($footer)
                    <div class="shrink-0 border-t border-sidebar-border p-3">
                        {{ $footer }}
                    </div>
                @endisset
            </aside>

            {{-- Main --}}
            <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
                <header class="flex h-14 shrink-0 items-center gap-3 border-b border-slate-200 bg-white px-4 lg:px-6">
                    <button
                        type="button"
                        class="rounded-md p-2 text-slate-600 hover:bg-slate-100 lg:hidden"
                        @click="$dispatch('open-drawer', 'mobile-nav')"
                        aria-label="{{ __('Toggle navigation') }}"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <button
                        type="button"
                        class="hidden rounded-md p-2 text-slate-600 hover:bg-slate-100 lg:inline-flex"
                        @click="collapsed = ! collapsed"
                        :aria-label="collapsed ? '{{ __('Expand sidebar') }}' : '{{ __('Collapse sidebar') }}'"
                    >
                        {{-- Expanded: sidebar will move left on click --}}
                        <svg
                            x-show="! collapsed"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                        {{-- Collapsed: sidebar will move right on click --}}
                        <svg
                            x-show="collapsed"
                            x-cloak
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12l-7.5 7.5" />
                        </svg>
                    </button>

                    <div class="min-w-0 flex-1">
                        @isset($header)
                            {{ $header }}
                        @endisset
                    </div>

                    @isset($actions)
                        {{ $actions }}
                    @endisset

                    <x-sidebar.user-menu
                        :profile-href="$profileHref"
                        :logout-action="$logoutAction"
                    />
                </header>

                <main class="min-h-0 flex-1 overflow-hidden">
                    <x-ui.scroll-area class="h-full" fade>
                        <div class="px-4 pb-4 pt-2 sm:px-6 sm:pb-6 sm:pt-3 lg:px-8 lg:pb-8 lg:pt-3">
                            {{ $slot }}
                        </div>
                    </x-ui.scroll-area>
                </main>
            </div>
        </div>

        @stack('modals')
        @stack('drawers')
    </body>
</html>

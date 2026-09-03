@props([
    'profileHref' => null,
    'logoutAction' => null,
])

<x-ui.popover
    side="bottom"
    align="end"
    width="48"
    content-class="p-1"
    close-on-content-click
>
    <x-slot:trigger>
        <button
            type="button"
            class="flex h-9 max-w-[10.5rem] items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 text-start shadow-sm transition-colors hover:bg-slate-50"
        >
            <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-navy text-xs font-semibold text-white">
                {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
            </span>
            <span class="hidden min-w-0 flex-1 lg:block">
                <span class="block truncate text-xs font-semibold leading-tight text-black">{{ Auth::user()->name }}</span>
                <span class="block truncate text-[11px] leading-tight text-slate-500">{{ Auth::user()->email }}</span>
            </span>
            <svg class="hidden size-3.5 shrink-0 text-slate-400 lg:block" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>
    </x-slot:trigger>

    <div class="px-2 py-1.5">
        <x-ui.popover.title>{{ Auth::user()->name }}</x-ui.popover.title>
        <x-ui.popover.description class="truncate">{{ Auth::user()->email }}</x-ui.popover.description>
    </div>

    @if ($profileHref)
        <x-ui.popover.item :href="$profileHref">{{ __('Profile') }}</x-ui.popover.item>
    @endif

    @if ($logoutAction)
        <form method="POST" action="{{ $logoutAction }}">
            @csrf
            <x-ui.popover.item as="button" type="submit">{{ __('Log Out') }}</x-ui.popover.item>
        </form>
    @endif
</x-ui.popover>

<div
    x-show="visible"
    x-transition:enter="transition-opacity ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed {{ $overlayPositionClass() }} {{ $overlayOnly ? 'z-40' : 'z-[60]' }} bg-navy/40 backdrop-blur-sm"
    @if ($closeOnOverlay)
        @click="closeDrawer()"
    @endif
    style="display: none;"
></div>

@unless ($overlayOnly)
    <div
        x-show="visible"
        class="fixed {{ $overlayOnly ? 'z-40' : 'z-[60]' }} flex {{ $panelPositionClass() }} {{ $maxWidthClass() }}"
        style="display: none;"
    >
        <div
            x-show="visible"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="{{ $side === 'left' ? '-translate-x-full' : ($side === 'bottom' ? 'translate-y-full' : 'translate-x-full') }}"
            x-transition:enter-end="translate-x-0 translate-y-0"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-x-0 translate-y-0"
            x-transition:leave-end="{{ $side === 'left' ? '-translate-x-full' : ($side === 'bottom' ? 'translate-y-full' : 'translate-x-full') }}"
            @click.stop
            class="pointer-events-auto flex h-full w-full flex-col overflow-hidden border-slate-200 bg-white shadow-2xl {{ $side === 'bottom' ? 'rounded-t-xl border-t' : ($side === 'left' ? 'border-e' : 'border-s border-slate-200') }}"
            role="dialog"
            aria-modal="true"
        >
            @isset($header)
                <div class="shrink-0 border-b border-slate-100">
                    {{ $header }}
                </div>
            @endisset

            <div class="flex min-h-0 flex-1 flex-col overflow-hidden">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="shrink-0 border-t border-slate-100">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
@endunless

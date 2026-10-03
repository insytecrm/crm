@php($config = $config())

<div
    x-data="uiPopover(@js($config))"
    @click.outside="close()"
    @keydown.escape.window="if (open) close()"
    {{ $attributes->merge(['class' => 'relative inline-flex']) }}
>
    <div
        @click="toggle()"
        :aria-expanded="open"
        aria-haspopup="dialog"
        class="inline-flex w-full"
    >
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @if ($closeOnContentClick)
            @click="close()"
        @endif
        role="dialog"
        class="{{ $panelPositionClass() }} {{ $originClass() }} {{ $widthClass() }} overflow-hidden rounded-2xl border {{ $panelClass() }} {{ $contentClass }}"
        style="display: none;"
    >
        {{ $slot }}
    </div>
</div>

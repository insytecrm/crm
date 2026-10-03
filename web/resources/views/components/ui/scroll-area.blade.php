<div
    data-slot="scroll-area"
    x-data="uiScrollArea"
    {{ $attributes->class([$rootClasses()]) }}
>
    <div
        data-slot="scroll-area-viewport"
        x-ref="viewport"
        tabindex="-1"
        @class([$viewportClasses()])
    >
        {{ $slot }}
    </div>
</div>

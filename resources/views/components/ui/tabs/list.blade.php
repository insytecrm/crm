@aware(['variant' => 'default'])

<div
    role="tablist"
    x-ref="tabList"
    data-slot="tabs-list"
    data-variant="{{ $variant }}"
    {{ $attributes->merge(['class' => $listClass()]) }}
>
    @if ($variant === 'default')
        <div
            class="pointer-events-none absolute top-1 bottom-1 rounded-[10px] bg-white shadow-sm transition-all duration-200 ease-out"
            :style="{ left: indicator.left, width: indicator.width }"
            aria-hidden="true"
        ></div>
    @endif
    {{ $slot }}
</div>

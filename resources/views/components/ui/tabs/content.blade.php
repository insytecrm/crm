@aware(['default' => ''])

<div
    role="tabpanel"
    x-show="activeTab === @js($value)"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-1"
    @if ($value !== $default)
        x-cloak
    @endif
    {{ $attributes->merge(['class' => 'text-sm outline-none']) }}
    data-slot="tabs-content"
>
    {{ $slot }}
</div>

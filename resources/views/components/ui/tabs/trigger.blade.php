@aware(['variant' => 'default'])

<button
    type="button"
    role="tab"
    @click="activeTab = @js($value); $nextTick(() => moveIndicator())"
    :aria-selected="activeTab === @js($value)"
    :class="activeTab === @js($value) ? @js($activeClass()) : ''"
    {{ $attributes->merge(['class' => $triggerClass()]) }}
>
    {{ $slot }}
</button>

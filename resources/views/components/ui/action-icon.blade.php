@php
    $ariaLabel = $attributes->get('aria-label') ?? $title;
    $target = $attributes->get('target');
@endphp

@if ($tag() === 'a')
    <a
        href="{{ $href }}"
        @if ($target) target="{{ $target }}" @endif
        @if ($title) title="{{ $title }}" @endif
        @if ($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
        {{ $attributes->except('target')->merge(['class' => $buttonClasses()]) }}
    >
        <x-ui.action-icon.glyph :icon="$icon" :class="$iconClasses()" />
    </a>
@else
    <button
        type="{{ $type }}"
        @if ($title) title="{{ $title }}" @endif
        @if ($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
        {{ $attributes->merge(['class' => $buttonClasses()]) }}
    >
        <x-ui.action-icon.glyph :icon="$icon" :class="$iconClasses()" />
    </button>
@endif

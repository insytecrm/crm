@php
    $ariaLabel = $attributes->get('aria-label') ?? $title;
@endphp

@if ($tag() === 'a')
    <a
        href="{{ $href }}"
        @if ($title) title="{{ $title }}" @endif
        @if ($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
        {{ $attributes->merge(['class' => $buttonClasses()]) }}
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

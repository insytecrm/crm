@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white dark:bg-gray-700'])

@php
$popoverAlign = match ($align) {
    'left' => 'start',
    'top' => 'center',
    default => 'end',
};

$popoverSide = $align === 'top' ? 'top' : 'bottom';
@endphp

<x-ui.popover
    :side="$popoverSide"
    :align="$popoverAlign"
    :width="$width"
    content-class="p-0"
    close-on-content-click
    {{ $attributes }}
>
    <x-slot:trigger>
        {{ $trigger }}
    </x-slot:trigger>

    <div class="{{ $contentClasses }}">
        {{ $content }}
    </div>
</x-ui.popover>

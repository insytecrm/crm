@props([
    'itemIds' => [],
])

<div
    x-data="manageableDataTable('platform-table', @js($itemIds), {})"
    {{ $attributes->class('') }}
>
    {{ $slot }}
</div>

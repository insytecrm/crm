@props([
    'dataTableKey',
    'dataTableItemIds' => [],
    'dataTableCustomValues' => [],
])

<div
    x-data="manageableDataTable(@js($dataTableKey), @js($dataTableItemIds), @js($dataTableCustomValues))"
    {{ $attributes->class('') }}
>
    {{ $slot }}
</div>

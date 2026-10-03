@props([
    'booking' => null,
])

@php
    $property = $booking?->property;
    $propertyLine = $property
        ? ($property->developer_name
            ? "{$property->project_name} · {$property->developer_name}"
            : $property->project_name)
        : '—';
    $configurationLine = $booking?->configuration_name ?? '—';
@endphp

<div class="min-w-0 max-w-[14rem] rounded-md border border-slate-100 bg-slate-50/90 px-2 py-1.5 shadow-sm">
    <p class="truncate text-xs font-semibold leading-snug text-slate-700" title="{{ $propertyLine }}">
        {{ $propertyLine }}
    </p>
    <p class="truncate text-xs leading-snug text-slate-500" title="{{ $configurationLine }}">
        {{ $configurationLine }}
    </p>
</div>

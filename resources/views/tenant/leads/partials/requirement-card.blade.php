@props([
    'lead',
])

@php
    $detailParts = collect([
        $lead->location,
        $lead->property_type?->label(),
        $lead->configuration,
    ])->filter(fn (?string $value): bool => filled($value));

    $detailsLine = $detailParts->isNotEmpty()
        ? $detailParts->implode(' · ')
        : '—';
@endphp

<div class="min-w-0 max-w-[14rem] rounded-md border border-slate-100 bg-slate-50/90 px-2 py-1.5 shadow-sm">
    <p class="truncate text-xs font-semibold leading-snug text-slate-700" title="{{ $lead->budget?->label() ?? '' }}">
        {{ $lead->budget?->label() ?? '—' }}
    </p>
    <p class="truncate text-xs leading-snug text-slate-500" title="{{ $detailsLine }}">
        {{ $detailsLine }}
    </p>
</div>

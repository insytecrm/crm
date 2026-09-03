@props([
    'disabled' => false,
])

@php
    $hasIcon = isset($icon);
    $hasSuffix = isset($suffix);
    $isNumberInput = ($attributes->get('type') ?? 'text') === 'number';
@endphp

<div class="relative">
    @if ($hasIcon)
        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3.5 text-slate-400">
            {{ $icon }}
        </span>
    @endif

    <input
        @disabled($disabled)
        @if ($isNumberInput) @wheel.prevent @endif
        {{ $attributes->merge([
            'class' => 'block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm placeholder:text-slate-400 focus:border-navy focus:ring-navy '.($hasIcon ? 'pl-10' : '').' '.($hasSuffix ? 'pr-10' : '').' '.($isNumberInput ? 'input-no-spin ' : ''),
        ]) }}
    >

    @if ($hasSuffix)
        <div class="absolute inset-y-0 right-0 z-10 flex items-center pr-3">
            {{ $suffix }}
        </div>
    @endif
</div>

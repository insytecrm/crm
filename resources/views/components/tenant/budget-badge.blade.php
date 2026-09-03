@props(['budget'])

@php
    $classes = match ($budget->value) {
        'below_50_lakh' => 'bg-sky-100 text-sky-700',
        '50_lakh_to_70_lakh' => 'bg-blue-100 text-blue-700',
        '70_lakh_to_90_lakh' => 'bg-indigo-100 text-indigo-700',
        '90_lakh_to_1_2_crore' => 'bg-violet-100 text-violet-700',
        '1_2_crore_to_1_5_crore' => 'bg-purple-100 text-purple-700',
        '1_5_crore_to_2_crore' => 'bg-fuchsia-100 text-fuchsia-700',
        '2_crore_to_3_crore' => 'bg-amber-100 text-amber-700',
        '3_crore_to_5_crore' => 'bg-orange-100 text-orange-700',
        'above_5_crore' => 'bg-emerald-100 text-emerald-700',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {$classes}"]) }}>
    {{ $budget->label() }}
</span>

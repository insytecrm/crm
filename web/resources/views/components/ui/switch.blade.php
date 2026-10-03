@props([
    'name',
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'label' => null,
    'description' => null,
])

<label {{ $attributes->merge(['class' => 'flex '.($description ? 'items-start' : 'items-center').' justify-between gap-4']) }}>
    <span class="min-w-0">
        @if ($label)
            <span class="block text-sm font-medium text-black">{{ $label }}</span>
        @endif
        @if ($description)
            <span class="mt-0.5 block text-sm text-slate-500">{{ $description }}</span>
        @endif
    </span>

    <span class="relative inline-flex h-6 w-11 shrink-0">
        <input
            type="checkbox"
            name="{{ $name }}"
            value="{{ $value }}"
            @checked($checked)
            @disabled($disabled)
            class="peer sr-only"
        >
        <span class="absolute inset-0 rounded-full bg-slate-200 transition-colors peer-checked:bg-navy peer-focus-visible:outline-none peer-focus-visible:ring-2 peer-focus-visible:ring-navy/40 peer-focus-visible:ring-offset-2 peer-disabled:cursor-not-allowed"></span>
        <span class="absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-5 peer-disabled:cursor-not-allowed"></span>
    </span>
</label>

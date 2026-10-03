@props([
    'value' => null,
    'required' => false,
])

<label {{ $attributes->merge(['class' => 'mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-500']) }}>
    {{ $value ?? $slot }}
    @if ($required)
        <span class="text-rose-500">*</span>
    @endif
</label>

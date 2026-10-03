@props(['label', 'value' => null])

<div>
    <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">{{ $label }}</dt>
    <dd class="mt-0.5 text-sm font-medium text-black">{{ filled($value) ? $value : '—' }}</dd>
</div>

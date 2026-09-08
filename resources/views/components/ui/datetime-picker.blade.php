<div {{ $attributes->class(['crm-datetime-picker w-full']) }}>
    <input
        type="{{ $inputType() }}"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ $normalizedValue() }}"
        @disabled($disabled)
        @required($required)
        class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400 [color-scheme:light]"
    >
</div>

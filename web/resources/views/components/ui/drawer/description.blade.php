@props(['class' => ''])

<p {{ $attributes->merge(['class' => 'text-sm text-slate-500 '.$class]) }}>
    {{ $slot }}
</p>

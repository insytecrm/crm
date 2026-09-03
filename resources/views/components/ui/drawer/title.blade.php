@props(['class' => ''])

<h2 {{ $attributes->merge(['class' => 'text-lg font-semibold leading-none tracking-tight text-black '.$class]) }}>
    {{ $slot }}
</h2>

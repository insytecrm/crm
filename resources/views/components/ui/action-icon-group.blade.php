@props([
    'gap' => 'gap-1.5',
])

<div {{ $attributes->merge(['class' => "inline-flex flex-nowrap items-center justify-end {$gap}"]) }} @click.stop>
    {{ $slot }}
</div>

@php($config = $classes())

@if ($config['tag'] === 'a')
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $config['classes']]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $config['classes']]) }}>
        {{ $slot }}
    </button>
@endif

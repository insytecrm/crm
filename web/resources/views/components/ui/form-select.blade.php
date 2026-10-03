@php
    $resolvedOptions = $normalizedOptions();
@endphp

<x-ui.select
    :name="$name"
    :id="$id"
    :options="$resolvedOptions"
    :value="$value"
    :placeholder="$placeholder"
    :required="$required"
    :disabled="$disabled"
    :min-menu-width="280"
    {{ $attributes }}
/>

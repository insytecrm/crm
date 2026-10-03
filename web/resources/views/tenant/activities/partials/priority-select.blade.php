@props([
    'name' => 'priority',
    'id' => 'priority',
    'value' => null,
    'required' => false,
])

@php
    $selected = old($name, $value ?? \App\Enums\ScheduledActivityPriority::Normal->value);
@endphp

<x-ui.form-select
    :id="$id"
    :name="$name"
    :value="$selected"
    :options="collect(\App\Enums\ScheduledActivityPriority::options())->map(fn ($option) => ['value' => $option->value, 'label' => $option->label()])->all()"
    :placeholder="__('Select priority')"
    :required="$required"
/>

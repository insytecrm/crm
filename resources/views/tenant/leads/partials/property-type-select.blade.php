@props([
    'id' => 'property_type',
    'name' => 'property_type',
    'value' => null,
])

<x-ui.select
    :id="$id"
    :name="$name"
    :options="collect(\App\Enums\PropertyType::cases())->map(fn ($type) => ['value' => $type->value, 'label' => $type->label()])->all()"
    :value="old($name, $value ?? '')"
    :placeholder="config('property-form.placeholders.property_type')"
/>

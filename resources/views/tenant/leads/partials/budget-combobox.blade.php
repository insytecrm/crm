@props([
    'id' => 'budget',
    'name' => 'budget',
    'value' => null,
])

<x-ui.select
    :id="$id"
    :name="$name"
    :options="collect(\App\Enums\LeadBudget::cases())->map(fn ($budget) => ['value' => $budget->value, 'label' => $budget->label()])->all()"
    :value="old($name, $value ?? '')"
    :placeholder="__('Select budget')"
/>

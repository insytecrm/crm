@props([
    'name' => 'configurations',
    'value' => [],
    'placeholders' => [],
    'hint' => null,
    'maxRows' => 50,
])

@php
    $initialRows = old($name, $value);

    if (! is_array($initialRows)) {
        $initialRows = [];
    }

    $fieldPlaceholders = array_merge([
        'name' => 'e.g. 2 BHK',
        'carpet_area_sqft' => 'Carpet Area (sq.ft)',
        'price' => 'Price (₹)',
        'unit_count' => 'Unit Count',
    ], $placeholders);
@endphp

<div
    x-data="uiConfigurationRepeater(@js([
        'name' => $name,
        'rows' => array_values($initialRows),
        'maxRows' => $maxRows,
        'placeholders' => $fieldPlaceholders,
        'removeLabel' => __('Remove configuration'),
    ]))"
    {{ $attributes->merge(['class' => 'configuration-repeater']) }}
>
    <div class="configuration-repeater-rows">
        <template x-for="(row, index) in rows" :key="index">
            <div class="configuration-repeater-row">
                <input
                    type="text"
                    class="configuration-repeater-input"
                    x-model="row.name"
                    :name="`${name}[${index}][name]`"
                    :placeholder="placeholders.name"
                    autocomplete="off"
                >

                <input
                    type="number"
                    class="configuration-repeater-input input-no-spin"
                    x-model="row.carpet_area_sqft"
                    :name="`${name}[${index}][carpet_area_sqft]`"
                    :placeholder="placeholders.carpet_area_sqft"
                    min="0"
                    @wheel.prevent
                >

                <input
                    type="number"
                    class="configuration-repeater-input input-no-spin"
                    x-model="row.price"
                    :name="`${name}[${index}][price]`"
                    :placeholder="placeholders.price"
                    min="0"
                    @wheel.prevent
                >

                <input
                    type="number"
                    class="configuration-repeater-input input-no-spin"
                    x-model="row.unit_count"
                    :name="`${name}[${index}][unit_count]`"
                    :placeholder="placeholders.unit_count"
                    min="0"
                    @wheel.prevent
                >

                <button
                    type="button"
                    class="configuration-repeater-remove"
                    @click="removeRow(index)"
                    :aria-label="removeLabel"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <button type="button" class="configuration-repeater-add" @click="addRow()">
        {{ __('+ Add Configuration') }}
    </button>

    @if ($hint)
        <p class="configuration-repeater-hint">{{ $hint }}</p>
    @endif
</div>

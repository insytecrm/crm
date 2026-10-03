@props([
    'name',
    'id' => null,
    'value' => [],
    'placeholder' => '',
    'hint' => null,
    'maxItems' => 50,
])

@php
    $inputId = $id ?? $name;
    $initialItems = old($name, $value);

    if (! is_array($initialItems)) {
        $initialItems = [];
    }
@endphp

<div
    x-data="uiTagInput(@js([
        'name' => $name,
        'items' => array_values($initialItems),
        'maxItems' => $maxItems,
        'removeLabel' => __('Remove'),
    ]))"
    {{ $attributes->merge(['class' => 'tag-input']) }}
>
    @if ($hint)
        <p class="tag-input-hint">{{ $hint }}</p>
    @endif

    <div class="tag-input-controls">
        <input
            type="text"
            id="{{ $inputId }}"
            x-model="draft"
            @keydown.enter.prevent="add()"
            placeholder="{{ $placeholder }}"
            class="tag-input-field"
            autocomplete="off"
        >

        <button type="button" class="tag-input-add" @click="add()">
            {{ __('Add') }}
        </button>
    </div>

    <div class="tag-input-list" x-show="items.length > 0" x-cloak>
        <template x-for="(item, index) in items" :key="`${item}-${index}`">
            <span class="tag-input-chip">
                <span x-text="item"></span>
                <button type="button" class="tag-input-remove" @click="remove(index)" :aria-label="removeLabel + ' ' + item">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
                <input type="hidden" :name="`${name}[]`" :value="item">
            </span>
        </template>
    </div>
</div>

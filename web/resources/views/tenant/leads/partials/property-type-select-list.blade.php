@props([
    'lead',
    'redirectToListing' => false,
    'portal' => false,
    'compact' => false,
])

@php
    $propertyTypeClassMap = [
        'apartment' => 'bg-sky-100 text-sky-700',
        'villa' => 'bg-emerald-100 text-emerald-700',
        'plot' => 'bg-amber-100 text-amber-700',
        'shop' => 'bg-violet-100 text-violet-700',
        'office' => 'bg-indigo-100 text-indigo-700',
    ];

    $currentPropertyType = $lead->property_type?->value ?? '';
    $minWidthClass = $compact ? 'min-w-0' : 'min-w-[7rem]';
    $triggerClasses = 'flex '.($compact ? 'h-7' : 'h-8').' '.$minWidthClass.' w-full items-center gap-1 rounded-lg border-0 '.($propertyTypeClassMap[$currentPropertyType] ?? 'bg-slate-100 text-slate-500').' px-2 text-left text-xs font-semibold shadow-sm ring-1 ring-inset ring-black/5 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy/30';
@endphp

<form
    method="POST"
    action="{{ route('tenant.leads.property-type.update', $lead) }}"
    class="inline {{ $minWidthClass }} {{ $compact ? 'max-w-[6.5rem]' : '' }}"
    @click.stop
    x-data="leadFieldSelect(@js([
        'field' => 'property_type',
        'value' => $currentPropertyType,
        'classes' => $propertyTypeClassMap,
        'widthClass' => $minWidthClass,
    ]))"
    x-on:selected="submitValue($event.detail)"
>
    @csrf
    @method('PATCH')
    @if ($redirectToListing)
        <input type="hidden" name="redirect_to_listing" value="1">
    @endif

    <x-ui.select
        :id="'lead-property-type-'.$lead->id"
        name="property_type"
        :options="collect(\App\Enums\PropertyType::cases())->map(fn ($type) => ['value' => $type->value, 'label' => $type->label()])->all()"
        :value="$currentPropertyType"
        :trigger-class="$triggerClasses"
        :placeholder="config('property-form.placeholders.property_type')"
        :aria-label="__('Property type for :name', ['name' => $lead->name])"
        :min-menu-width="160"
        :portal="$portal"
        class="{{ $compact ? 'w-auto max-w-[6.5rem]' : 'w-auto min-w-[7rem]' }}"
    />
</form>

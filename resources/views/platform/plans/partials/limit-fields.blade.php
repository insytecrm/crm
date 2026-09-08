@php
    $draftLimits = old('limits', $draft['limits'] ?? $plan?->limits ?? []);
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    @foreach ($limits as $limit)
        <div>
            <x-input-label :for="'limit-'.$limit->value" :value="$limit->label().($limit->unit() ?? '')" />
            <x-text-input
                :id="'limit-'.$limit->value"
                :name="'limits['.$limit->value.']'"
                type="number"
                min="0"
                class="mt-1 block w-full"
                :value="old('limits.'.$limit->value, $draftLimits[$limit->value] ?? '')"
                placeholder="{{ __('Unlimited') }}"
            />
            <p class="mt-1 text-xs text-slate-400">{{ __('Leave blank for unlimited.') }}</p>
            <x-input-error class="mt-2" :messages="$errors->get('limits.'.$limit->value)" />
        </div>
    @endforeach
</div>

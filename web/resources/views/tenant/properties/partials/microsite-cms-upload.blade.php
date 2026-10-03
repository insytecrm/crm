@props([
    'name',
    'label',
    'file' => null,
    'previewUrl' => null,
    'removeName' => null,
    'multiple' => false,
])

<div>
    <x-input-label :for="$name" :value="$label" />
    @if ($previewUrl)
        <div class="mt-2 overflow-hidden rounded-lg border border-slate-100">
            <img src="{{ $previewUrl }}" alt="" class="h-28 w-full object-cover">
        </div>
    @elseif (is_array($file) && filled($file['name'] ?? null))
        <p class="mt-2 text-xs text-slate-500">{{ $file['name'] }}</p>
    @endif
    <input
        id="{{ $name }}"
        type="file"
        name="{{ $multiple ? $name.'[]' : $name }}"
        @if ($multiple) multiple @endif
        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
        class="property-form-file mt-2"
    >
    @if ($removeName && ($previewUrl || $file))
        <label class="mt-2 flex items-center gap-2 text-xs text-slate-600">
            <input type="checkbox" name="{{ $removeName }}" value="1">
            {{ __('Remove current file') }}
        </label>
    @endif
    <x-input-error class="mt-1" :messages="$errors->get($name)" />
</div>

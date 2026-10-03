@props([
    'section',
    'content',
    'defaults',
    'showBody' => false,
    'showButton' => false,
    'showVideo' => false,
    'showMap' => false,
    'bodyLabel' => null,
    'buttonLabel' => null,
])

@php
    $fields = $content[$section] ?? [];
    $defaultHeadline = $defaults[$section]['headline'] ?? null;
    $defaultSubheadline = $defaults[$section]['subheadline'] ?? null;
    $defaultBody = $defaults[$section]['body'] ?? null;
    $defaultButton = $defaults[$section]['button_label'] ?? null;
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-input-label :for="'sections_'.$section.'_headline'" :value="__('Headline')" />
        <x-auth.icon-input
            :id="'sections_'.$section.'_headline'"
            :name="'sections['.$section.'][headline]'"
            :value="old('sections.'.$section.'.headline', $fields['headline'] ?? '')"
            :placeholder="$defaultHeadline"
        />
        @if ($defaultHeadline)
            <p class="mt-1 text-xs text-slate-400">{{ __('Default') }}: {{ $defaultHeadline }}</p>
        @endif
        <x-input-error class="mt-1" :messages="$errors->get('sections.'.$section.'.headline')" />
    </div>
    <div>
        <x-input-label :for="'sections_'.$section.'_subheadline'" :value="__('Subheadline')" />
        <x-auth.icon-input
            :id="'sections_'.$section.'_subheadline'"
            :name="'sections['.$section.'][subheadline]'"
            :value="old('sections.'.$section.'.subheadline', $fields['subheadline'] ?? '')"
            :placeholder="$defaultSubheadline"
        />
        @if ($defaultSubheadline)
            <p class="mt-1 text-xs text-slate-400">{{ __('Default') }}: {{ $defaultSubheadline }}</p>
        @endif
        <x-input-error class="mt-1" :messages="$errors->get('sections.'.$section.'.subheadline')" />
    </div>
</div>

@if ($showBody)
    <div class="mt-4">
        <x-input-label :for="'sections_'.$section.'_body'" :value="$bodyLabel ?? __('Copy')" />
        <textarea
            id="{{ 'sections_'.$section.'_body' }}"
            name="{{ 'sections['.$section.'][body]' }}"
            rows="4"
            placeholder="{{ $defaultBody }}"
            class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-black shadow-sm placeholder:text-slate-400 focus:border-navy focus:ring-navy"
        >{{ old('sections.'.$section.'.body', $fields['body'] ?? '') }}</textarea>
        <p class="mt-1 text-xs text-slate-400">{{ __('Leave blank to keep the template default. This does not change property information.') }}</p>
        <x-input-error class="mt-1" :messages="$errors->get('sections.'.$section.'.body')" />
    </div>
@endif

@if ($showButton)
    <div class="mt-4">
        <x-input-label :for="'sections_'.$section.'_button_label'" :value="$buttonLabel ?? __('Button label')" />
        <x-auth.icon-input
            :id="'sections_'.$section.'_button_label'"
            :name="'sections['.$section.'][button_label]'"
            :value="old('sections.'.$section.'.button_label', $fields['button_label'] ?? '')"
            :placeholder="$defaultButton"
        />
        @if ($defaultButton)
            <p class="mt-1 text-xs text-slate-400">{{ __('Default') }}: {{ $defaultButton }}</p>
        @endif
        <x-input-error class="mt-1" :messages="$errors->get('sections.'.$section.'.button_label')" />
    </div>
@endif

@if ($showVideo)
    <div class="mt-4">
        <x-input-label :for="'sections_'.$section.'_video_url'" :value="__('Video URL')" />
        <x-auth.icon-input
            :id="'sections_'.$section.'_video_url'"
            :name="'sections['.$section.'][video_url]'"
            :value="old('sections.'.$section.'.video_url', $fields['video_url'] ?? '')"
            placeholder="https://www.youtube.com/watch?v=..."
        />
        <p class="mt-1 text-xs text-slate-400">{{ __('YouTube or Vimeo link. Replaces the image in this section when set.') }}</p>
        <x-input-error class="mt-1" :messages="$errors->get('sections.'.$section.'.video_url')" />
    </div>
@endif

@if ($showMap)
    <div class="mt-4">
        <x-input-label :for="'sections_'.$section.'_map_url'" :value="__('Map link')" />
        <x-auth.icon-input
            :id="'sections_'.$section.'_map_url'"
            :name="'sections['.$section.'][map_url]'"
            :value="old('sections.'.$section.'.map_url', $fields['map_url'] ?? '')"
            placeholder="https://maps.google.com/..."
        />
        <p class="mt-1 text-xs text-slate-400">{{ __('Paste a Google Maps link. The project location from property information is used when this is empty.') }}</p>
        <x-input-error class="mt-1" :messages="$errors->get('sections.'.$section.'.map_url')" />
    </div>
@endif

@php
    $slug = $property->microsite_slug;
    $mediaUrl = fn (string $key): string => route('tenant.projects.microsite.media', ['slug' => $slug, 'key' => $key]);
@endphp

<x-tenant-layout :title="__('Manage microsite') . ' | ' . $property->project_name">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('tenant.properties.index') }}" class="text-xs font-medium text-black hover:underline">&larr; {{ __('Back to Properties') }}</a>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-black">{{ __('Manage microsite') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Replace headlines, buttons, fonts, lead form, media, and theme for this project page. Property facts stay on the property form.') }}</p>
        </div>
        <x-ui.button variant="outline" size="sm" :href="$property->micrositeUrl()" target="_blank" rel="noopener noreferrer">
            {{ __('View site') }}
        </x-ui.button>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('tenant.properties.microsite.content.update', $property) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PATCH')

        <x-tenant.form-section-card :title="__('Theme, fonts & contact')" compact>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <x-input-label for="theme_preset" :value="__('Colour theme')" />
                    <select id="theme_preset" name="theme_preset" class="mt-1 block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm focus:border-navy focus:ring-navy">
                        @foreach ($themes as $theme)
                            <option value="{{ $theme->value }}" @selected(old('theme_preset', $microsite->theme()->value) === $theme->value)>{{ $theme->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-1" :messages="$errors->get('theme_preset')" />
                </div>
                <div>
                    <x-input-label for="theme_accent" :value="__('Accent colour')" />
                    <input id="theme_accent" name="theme_accent" type="color" value="{{ old('theme_accent', $microsite->theme_accent ?: $microsite->theme()->tokens()['accent']) }}" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white p-1">
                    <x-input-error class="mt-1" :messages="$errors->get('theme_accent')" />
                </div>
                <div>
                    <x-input-label for="cta_label" :value="__('Header / mobile button')" />
                    <x-auth.icon-input id="cta_label" name="cta_label" :value="old('cta_label', $microsite->cta_label)" :placeholder="$defaults['cta_label']" />
                    <p class="mt-1 text-xs text-slate-400">{{ __('Used in the sticky header and mobile bar. Section buttons can override separately.') }}</p>
                    <x-input-error class="mt-1" :messages="$errors->get('cta_label')" />
                </div>
                <div>
                    <x-input-label for="font_primary" :value="__('Primary font')" />
                    <select id="font_primary" name="font_primary" class="mt-1 block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm focus:border-navy focus:ring-navy">
                        @foreach ($primaryFonts as $font)
                            <option value="{{ $font->value }}" @selected(old('font_primary', $microsite->primaryFont()->value) === $font->value)>{{ $font->label() }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400">{{ __('Body text and UI labels.') }}</p>
                    <x-input-error class="mt-1" :messages="$errors->get('font_primary')" />
                </div>
                <div>
                    <x-input-label for="font_secondary" :value="__('Secondary font')" />
                    <select id="font_secondary" name="font_secondary" class="mt-1 block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm focus:border-navy focus:ring-navy">
                        @foreach ($secondaryFonts as $font)
                            <option value="{{ $font->value }}" @selected(old('font_secondary', $microsite->secondaryFont()->value) === $font->value)>{{ $font->label() }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400">{{ __('Display headlines and titles.') }}</p>
                    <x-input-error class="mt-1" :messages="$errors->get('font_secondary')" />
                </div>
                <div>
                    <x-input-label for="phone" :value="__('Public phone')" />
                    <x-auth.icon-input id="phone" name="phone" :value="old('phone', $microsite->phone)" placeholder="+91 98xxxxxxxx" />
                    <x-input-error class="mt-1" :messages="$errors->get('phone')" />
                </div>
                <div>
                    <x-input-label for="whatsapp" :value="__('WhatsApp')" />
                    <x-auth.icon-input id="whatsapp" name="whatsapp" :value="old('whatsapp', $microsite->whatsapp)" placeholder="9198xxxxxxxx" />
                    <x-input-error class="mt-1" :messages="$errors->get('whatsapp')" />
                </div>
                @include('tenant.properties.partials.microsite-cms-upload', [
                    'name' => 'logo_image',
                    'label' => __('Logo'),
                    'file' => $media['logo'],
                    'previewUrl' => $media['logo'] ? $mediaUrl('logo') : null,
                    'removeName' => 'remove_logo',
                ])
            </div>
        </x-tenant.form-section-card>

        <x-tenant.form-section-card :title="__('Lead form')" compact>
            <p class="mb-4 text-xs text-slate-400">{{ __('Customise the enquire popup. Leave labels blank to use defaults.') }}</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="lead_form_title" :value="__('Form title')" />
                    <x-auth.icon-input id="lead_form_title" name="lead_form[title]" :value="old('lead_form.title', $leadForm['title'])" :placeholder="$defaults['lead_form']['title']" />
                    <x-input-error class="mt-1" :messages="$errors->get('lead_form.title')" />
                </div>
                <div>
                    <x-input-label for="lead_form_submit_label" :value="__('Submit button')" />
                    <x-auth.icon-input id="lead_form_submit_label" name="lead_form[submit_label]" :value="old('lead_form.submit_label', $leadForm['submit_label'])" :placeholder="$defaults['lead_form']['submit_label']" />
                    <x-input-error class="mt-1" :messages="$errors->get('lead_form.submit_label')" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="lead_form_subtitle" :value="__('Form subtitle')" />
                    <x-auth.icon-input id="lead_form_subtitle" name="lead_form[subtitle]" :value="old('lead_form.subtitle', $leadForm['subtitle'])" :placeholder="$defaults['lead_form']['subtitle']" />
                    <x-input-error class="mt-1" :messages="$errors->get('lead_form.subtitle')" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="lead_form_success_message" :value="__('Success message')" />
                    <x-auth.icon-input id="lead_form_success_message" name="lead_form[success_message]" :value="old('lead_form.success_message', $leadForm['success_message'])" :placeholder="$defaults['lead_form']['success_message']" />
                    <x-input-error class="mt-1" :messages="$errors->get('lead_form.success_message')" />
                </div>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                @foreach ([
                    'name' => __('Name'),
                    'phone' => __('Phone'),
                    'email' => __('Email'),
                    'configuration' => __('Configuration'),
                ] as $field => $defaultLabel)
                    <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-medium text-black">{{ $defaultLabel }}</p>
                            <label class="flex items-center gap-2 text-xs text-slate-600">
                                <input type="checkbox" name="lead_form[show_{{ $field }}]" value="1" @checked(old('lead_form.show_'.$field, $leadForm['show_'.$field]))>
                                {{ __('Show') }}
                            </label>
                        </div>
                        <div class="mt-3">
                            <x-input-label :for="'lead_form_label_'.$field" :value="__('Field label')" />
                            <x-auth.icon-input
                                :id="'lead_form_label_'.$field"
                                :name="'lead_form[label_'.$field.']'"
                                :value="old('lead_form.label_'.$field, $leadForm['label_'.$field])"
                                :placeholder="$defaults['lead_form']['label_'.$field]"
                            />
                        </div>
                        <label class="mt-3 flex items-center gap-2 text-xs text-slate-600">
                            <input type="checkbox" name="lead_form[require_{{ $field }}]" value="1" @checked(old('lead_form.require_'.$field, $leadForm['require_'.$field]))>
                            {{ __('Required') }}
                        </label>
                    </div>
                @endforeach
            </div>
        </x-tenant.form-section-card>

        <x-tenant.form-section-card :title="__('Hero')" compact>
            @include('tenant.properties.partials.microsite-cms-section', [
                'section' => 'hero',
                'content' => $content,
                'defaults' => $defaults,
                'showButton' => true,
                'showVideo' => true,
                'buttonLabel' => __('Hero button'),
            ])
            <div class="mt-4">
                @include('tenant.properties.partials.microsite-cms-upload', [
                    'name' => 'hero_image',
                    'label' => __('Replace hero image'),
                    'file' => $media['hero'],
                    'previewUrl' => $media['hero'] ? $mediaUrl('hero') : null,
                    'removeName' => 'remove_hero',
                ])
            </div>
        </x-tenant.form-section-card>

        <x-tenant.form-section-card :title="__('Highlights')" compact>
            @include('tenant.properties.partials.microsite-cms-section', [
                'section' => 'highlights',
                'content' => $content,
                'defaults' => $defaults,
            ])
        </x-tenant.form-section-card>

        <x-tenant.form-section-card :title="__('About')" compact>
            @include('tenant.properties.partials.microsite-cms-section', [
                'section' => 'about',
                'content' => $content,
                'defaults' => $defaults,
                'showBody' => true,
            ])
            <div class="mt-4">
                @include('tenant.properties.partials.microsite-cms-upload', [
                    'name' => 'about_image',
                    'label' => __('About image'),
                    'file' => $media['about'],
                    'previewUrl' => $media['about'] ? $mediaUrl('about') : null,
                    'removeName' => 'remove_about',
                ])
            </div>
        </x-tenant.form-section-card>

        <x-tenant.form-section-card :title="__('Configurations')" compact>
            @include('tenant.properties.partials.microsite-cms-section', [
                'section' => 'configurations',
                'content' => $content,
                'defaults' => $defaults,
                'showButton' => true,
                'buttonLabel' => __('Configuration card button'),
            ])
            <p class="mt-3 text-xs text-slate-400">{{ __('Unit types, areas, and prices come from property information and are grouped automatically.') }}</p>
        </x-tenant.form-section-card>

        <x-tenant.form-section-card :title="__('Amenities')" compact>
            @include('tenant.properties.partials.microsite-cms-section', [
                'section' => 'amenities',
                'content' => $content,
                'defaults' => $defaults,
            ])
            <p class="mt-3 text-xs text-slate-400">{{ __('Amenity names stay on the property form. Use this section only for headlines.') }}</p>
        </x-tenant.form-section-card>

        <x-tenant.form-section-card :title="__('Visuals')" compact>
            @include('tenant.properties.partials.microsite-cms-section', [
                'section' => 'visuals',
                'content' => $content,
                'defaults' => $defaults,
                'showButton' => true,
                'showVideo' => true,
                'buttonLabel' => __('Download brochure button'),
            ])
            <div class="mt-4">
                <x-input-label for="gallery" :value="__('Add gallery images')" />
                <input id="gallery" type="file" name="gallery[]" multiple accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="property-form-file mt-2">
                <x-input-error class="mt-1" :messages="$errors->get('gallery')" />
                @if (($media['gallery'] ?? []) !== [])
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        @foreach ($media['gallery'] as $index => $file)
                            <label class="overflow-hidden rounded-lg border border-slate-100">
                                <img src="{{ $mediaUrl('gallery-'.$index) }}" alt="" class="h-24 w-full object-cover">
                                <span class="flex items-center gap-2 px-2 py-2 text-xs text-slate-600">
                                    <input type="checkbox" name="remove_gallery[]" value="{{ $file['id'] }}">
                                    {{ __('Remove') }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-tenant.form-section-card>

        <x-tenant.form-section-card :title="__('Location')" compact>
            @include('tenant.properties.partials.microsite-cms-section', [
                'section' => 'location',
                'content' => $content,
                'defaults' => $defaults,
                'showMap' => true,
            ])
            <div class="mt-4">
                @include('tenant.properties.partials.microsite-cms-upload', [
                    'name' => 'location_image',
                    'label' => __('Location image'),
                    'file' => $media['location'],
                    'previewUrl' => $media['location'] ? $mediaUrl('location') : null,
                    'removeName' => 'remove_location',
                ])
            </div>
        </x-tenant.form-section-card>

        <x-tenant.form-section-card :title="__('Developer')" compact>
            @include('tenant.properties.partials.microsite-cms-section', [
                'section' => 'developer',
                'content' => $content,
                'defaults' => $defaults,
                'showBody' => true,
            ])
            <div class="mt-4">
                @include('tenant.properties.partials.microsite-cms-upload', [
                    'name' => 'developer_image',
                    'label' => __('Developer image'),
                    'file' => $media['developer'],
                    'previewUrl' => $media['developer'] ? $mediaUrl('developer') : null,
                    'removeName' => 'remove_developer',
                ])
            </div>
        </x-tenant.form-section-card>

        <x-tenant.form-section-card :title="__('Call to action')" compact>
            @include('tenant.properties.partials.microsite-cms-section', [
                'section' => 'cta',
                'content' => $content,
                'defaults' => $defaults,
                'showButton' => true,
                'buttonLabel' => __('Final CTA button'),
            ])
        </x-tenant.form-section-card>

        <div class="flex justify-end gap-2">
            <x-ui.button variant="outline" size="sm" :href="route('tenant.properties.index')">{{ __('Cancel') }}</x-ui.button>
            <x-ui.button type="submit" size="sm">{{ __('Save microsite') }}</x-ui.button>
        </div>
    </form>
</x-tenant-layout>

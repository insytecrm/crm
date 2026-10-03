@props([
    'property' => null,
    'live' => false,
])

@php
    $checked = (bool) old('microsite_enabled', $property?->hasMicrosite() ?? false);
    $canPublishMicrosites = tenant()?->canPublishMicrosites() ?? true;
@endphp

<div class="space-y-3">
    @unless ($canPublishMicrosites)
        <div class="rounded-xl border border-amber-100 bg-amber-50 px-3 py-2.5 text-sm text-amber-800">
            {{ __('Verify your website domain in Settings → Domains before publishing microsites.') }}
            <a href="{{ route('tenant.settings.index', ['tab' => 'domains']) }}" class="ml-1 font-medium text-navy hover:underline">
                {{ __('Open Domains') }}
            </a>
        </div>
    @endunless

    @if ($live && $property)
        <div
            class="space-y-3"
            x-data="propertyMicrositeSwitch(@js([
                'enabled' => $property->hasMicrosite(),
                'url' => $property->micrositeUrl(),
                'updateUrl' => route('tenant.properties.microsite.update', $property),
                'canPublish' => $canPublishMicrosites,
            ]))"
        >
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div
                    :class="saving && 'pointer-events-none opacity-60'"
                    class="min-w-0 flex-1"
                >
                    <label class="flex items-center justify-between gap-4 rounded-xl border border-slate-100 bg-white px-3 py-2.5">
                        <span class="block text-sm font-medium text-black">{{ __('Microsite') }}</span>
                        <span class="relative inline-flex h-6 w-11 shrink-0">
                            <input
                                type="checkbox"
                                class="peer sr-only"
                                x-model="enabled"
                                @change="save()"
                                @disabled(! $canPublishMicrosites && ! $property->hasMicrosite())
                            >
                            <span class="absolute inset-0 rounded-full bg-slate-200 transition-colors peer-checked:bg-navy peer-focus-visible:outline-none peer-focus-visible:ring-2 peer-focus-visible:ring-navy/40 peer-focus-visible:ring-offset-2 peer-disabled:opacity-50"></span>
                            <span class="absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-5"></span>
                        </span>
                    </label>
                </div>
                <x-ui.button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="shrink-0"
                    x-show="! enabled"
                    disabled
                >
                    {{ __('Manage') }}
                </x-ui.button>
                <x-ui.button
                    variant="outline"
                    size="sm"
                    class="shrink-0"
                    x-cloak
                    x-show="enabled"
                    :href="route('tenant.properties.microsite.manage', $property)"
                >
                    {{ __('Manage') }}
                </x-ui.button>
            </div>
            <template x-if="enabled && url">
                <a
                    :href="url"
                    x-text="url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="block truncate text-xs font-medium text-navy hover:underline"
                ></a>
            </template>
        </div>
    @else
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="min-w-0 flex-1">
                <input type="hidden" name="microsite_enabled" value="0">
                <x-ui.switch
                    name="microsite_enabled"
                    value="1"
                    :label="__('Microsite')"
                    :checked="$checked"
                    :disabled="! $canPublishMicrosites"
                    class="items-center rounded-xl border border-slate-100 bg-white px-3 py-2.5"
                />
            </div>
            <x-ui.button type="button" variant="outline" size="sm" class="shrink-0" disabled>
                {{ __('Manage') }}
            </x-ui.button>
        </div>
    @endif
</div>

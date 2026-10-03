@php
    use App\Enums\PlanFeature;
    use App\Enums\PlanPack;

    $draftFeatures = old('features', $draft['features'] ?? $plan?->features ?? []);
    $draftPacks = old('packs', $draft['packs'] ?? $plan?->packs ?? []);
    $draftCapabilities = collect(old('capabilities', $draft['capabilities'] ?? $plan?->capabilities ?? []));
@endphp

<div
    class="space-y-4"
    x-data="{
        packs: @js($draftPacks),
    }"
>
    <div class="space-y-3">
        @foreach ($modules as $module)
            @php
                $enabled = (bool) ($draftFeatures[$module->value] ?? false);
            @endphp
            <div class="rounded-xl border border-slate-200 px-4 py-3">
                <label class="flex items-center justify-between gap-4">
                    <span>
                        <span class="font-medium text-black">{{ $module->label() }}</span>
                        <span class="mt-0.5 block text-sm text-slate-500">{{ $module->description() }}</span>
                    </span>
                    <input
                        type="checkbox"
                        name="features[{{ $module->value }}]"
                        value="1"
                        class="rounded border-slate-300 text-navy focus:ring-navy"
                        @checked($enabled)
                    >
                </label>

                @if ($module->isPackable())
                    <div class="mt-3 flex flex-wrap gap-3 border-t border-slate-100 pt-3 text-sm">
                        @foreach ($packs as $pack)
                            <label class="inline-flex items-center gap-2">
                                <input
                                    type="radio"
                                    name="packs[{{ $module->value }}]"
                                    value="{{ $pack->value }}"
                                    class="text-navy focus:ring-navy"
                                    x-model="packs['{{ $module->value }}']"
                                    @checked(($draftPacks[$module->value] ?? 'basic') === $pack->value)
                                >
                                <span>{{ $pack->label() }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-3 space-y-2" x-show="packs['{{ $module->value }}'] === 'custom'" x-cloak>
                        @foreach (($capabilitiesByFeature[$module->value] ?? []) as $capability)
                            <label class="flex items-center justify-between gap-3 text-sm">
                                <span class="text-black">
                                    {{ $capability['label'] }}
                                    @unless ($capability['shipped'])
                                        <span class="ms-1 text-xs text-slate-400">{{ __('Coming soon') }}</span>
                                    @endunless
                                </span>
                                <input
                                    type="checkbox"
                                    name="capabilities[]"
                                    value="{{ $capability['value'] }}"
                                    class="rounded border-slate-300 text-navy focus:ring-navy"
                                    @checked($draftCapabilities->contains($capability['value']))
                                >
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <x-platform.panel :title="__('Integrations')" compact>
        <input type="hidden" name="features[integrations]" value="0">
        <label class="mb-3 flex items-center justify-between gap-4">
            <span class="font-medium text-black">{{ __('Enable integrations for this plan') }}</span>
            <input
                type="checkbox"
                name="features[integrations]"
                value="1"
                class="rounded border-slate-300 text-navy focus:ring-navy"
                @checked((bool) ($draftFeatures[PlanFeature::Integrations->value] ?? false))
            >
        </label>
        <div class="space-y-2">
            @foreach ($integrations as $integration)
                <label class="flex items-center justify-between gap-3 text-sm">
                    <span class="text-black">
                        {{ $integration->label() }}
                        @unless ($integration->isShipped())
                            <span class="ms-1 text-xs text-slate-400">{{ __('Coming soon') }}</span>
                        @endunless
                    </span>
                    <input
                        type="checkbox"
                        name="capabilities[]"
                        value="{{ $integration->value }}"
                        class="rounded border-slate-300 text-navy focus:ring-navy"
                        @checked($draftCapabilities->contains($integration->value))
                    >
                </label>
            @endforeach
        </div>
    </x-platform.panel>
</div>

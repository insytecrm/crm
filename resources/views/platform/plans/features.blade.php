<x-app-layout :title="__('Features') . ' · ' . $plan->name . ' | InSyte CRM'">
    <x-platform.plan-shell :plan="$plan" :shell="$shell">
        <div class="mb-4 flex justify-end">
            <x-ui.button variant="default" :href="route('platform.plans.edit', $plan).'#features'">
                {{ __('Edit Features') }}
            </x-ui.button>
        </div>

        <div class="space-y-3">
            @foreach ($featureStates as $feature)
                <x-platform.panel compact>
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-semibold text-black">{{ $feature['label'] }}</h2>
                        <span class="text-sm {{ $feature['included'] ? 'text-emerald-700' : 'text-slate-400' }}">
                            {{ $feature['included'] ? ($feature['pack'] ? $feature['pack'] : __('Included')) : __('Not Included') }}
                        </span>
                    </div>
                    @if (count($feature['capabilities']) > 0)
                        <ul class="mt-3 space-y-1.5 text-sm">
                            @foreach ($feature['capabilities'] as $capability)
                                <li class="flex items-center justify-between gap-3">
                                    <span class="text-slate-600">
                                        {{ $capability['label'] }}
                                        @if ($capability['coming_soon'])
                                            <span class="text-xs text-slate-400">{{ __('Coming soon') }}</span>
                                        @endif
                                    </span>
                                    <span class="{{ $capability['included'] ? 'text-emerald-600' : 'text-slate-300' }}">
                                        {{ $capability['included'] ? __('● Included') : __('○ Not Included') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-platform.panel>
            @endforeach
        </div>

        <div class="mt-4">
            @include('platform.plans.partials.preset-editor', ['presets' => $presets])
        </div>
    </x-platform.plan-shell>
</x-app-layout>

<x-platform.panel :title="__('What Basic and Advanced mean')" compact>
    <p class="mb-4 text-sm text-slate-500">
        {{ __('These packs apply to every plan that uses Basic or Advanced. Change them here when you want a different meaning.') }}
    </p>

    <form method="POST" action="{{ route('platform.plans.presets.update') }}" class="space-y-5">
        @csrf

        @foreach ($presets as $module => $preset)
            <div class="rounded-xl border border-slate-100 p-4">
                <h3 class="font-semibold text-black">{{ $preset['label'] }}</h3>
                <div class="mt-3 grid gap-4 md:grid-cols-2">
                    @foreach (['basic' => __('Basic'), 'advanced' => __('Advanced')] as $pack => $packLabel)
                        <div>
                            <p class="mb-2 text-sm font-medium text-slate-600">{{ $packLabel }}</p>
                            <div class="space-y-2">
                                @foreach ($preset['options'] as $option)
                                    <label class="flex items-center justify-between gap-3 text-sm">
                                        <span>{{ $option['label'] }}</span>
                                        <input
                                            type="checkbox"
                                            name="presets[{{ $module }}][{{ $pack }}][]"
                                            value="{{ $option['value'] }}"
                                            class="rounded border-slate-300 text-navy focus:ring-navy"
                                            @checked(in_array($option['value'], $preset[$pack], true))
                                        >
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex justify-end">
            <x-ui.button type="submit" variant="outline">{{ __('Save pack meaning') }}</x-ui.button>
        </div>
    </form>
</x-platform.panel>

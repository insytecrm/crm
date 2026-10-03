<x-app-layout :title="__('Limits') . ' · ' . $plan->name . ' | InSyte CRM'">
    <x-platform.plan-shell :plan="$plan" :shell="$shell">
        <div class="mb-4 flex justify-end">
            <x-ui.button variant="default" :href="route('platform.plans.edit', $plan).'#limits'">
                {{ __('Edit Limits') }}
            </x-ui.button>
        </div>

        <x-platform.panel :title="__('Limits')" compact>
            <dl class="space-y-3 text-sm">
                @foreach ($limitRows as $row)
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">{{ $row['label'] }}</dt>
                        <dd class="font-medium text-black">
                            @if ($row['value'] === null)
                                {{ __('Unlimited') }}
                            @else
                                {{ number_format($row['value']) }}{{ $row['unit'] }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        </x-platform.panel>
    </x-platform.plan-shell>
</x-app-layout>

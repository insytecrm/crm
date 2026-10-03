<x-app-layout :title="__('Usage') . ' · ' . $tenant->name . ' | InSyte CRM'">
    <x-platform.partner-shell :tenant="$tenant" :shell="$shell">
        @if (count($usage['warnings']) > 0)
            <div class="mb-4 space-y-2">
                @foreach ($usage['warnings'] as $warning)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                        {{ $warning['message'] }}
                    </div>
                @endforeach
            </div>
        @endif

        <div class="grid gap-4 lg:grid-cols-2">
            <x-platform.panel :title="__('Plan Usage')" compact>
                <div class="space-y-5">
                    @foreach ($usage['meters'] as $meter)
                        <div>
                            <div class="mb-1.5 flex items-center justify-between gap-4 text-sm">
                                <p class="font-medium text-black">{{ $meter['label'] }}</p>
                                <p class="tabular-nums text-slate-600">
                                    {{ $meter['used_label'] }}@if ($meter['limit_label']) / {{ $meter['limit_label'] }}@elseif ($meter['used'] !== null) / —@endif
                                </p>
                            </div>
                            <x-platform.usage-bar :percent="$meter['percent']" />
                        </div>
                    @endforeach
                </div>
            </x-platform.panel>

            <x-platform.panel :title="__('Activity This Month')" compact>
                <dl class="space-y-3 text-sm">
                    @foreach ($usage['month_activity'] as $row)
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-slate-500">{{ $row['label'] }}</dt>
                            <dd class="font-medium tabular-nums text-black">{{ $row['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-platform.panel>
        </div>
    </x-platform.partner-shell>
</x-app-layout>

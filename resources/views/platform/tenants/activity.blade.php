<x-app-layout :title="__('Activity') . ' · ' . $tenant->name . ' | InSyte CRM'">
    <x-platform.partner-shell :tenant="$tenant" :shell="$shell">
        <div class="mb-4 flex flex-wrap gap-2">
            @foreach ($activity['filters'] as $filter)
                <a
                    href="{{ $filter['href'] }}"
                    @class([
                        'inline-flex h-8 items-center rounded-lg border px-3.5 text-xs font-semibold transition-colors',
                        'border-navy bg-navy text-white' => $filter['active'],
                        'border-slate-200 bg-white text-black hover:bg-slate-50' => ! $filter['active'],
                    ])
                >
                    {{ $filter['label'] }}
                </a>
            @endforeach
        </div>

        <x-platform.panel compact>
            @if (count($activity['groups']) === 0)
                <p class="text-sm text-slate-500">{{ __('No activity found for this filter.') }}</p>
            @else
                <div class="space-y-6">
                    @foreach ($activity['groups'] as $group)
                        <div>
                            <h3 class="mb-3 text-sm font-semibold text-slate-500">{{ $group['label'] }}</h3>
                            <ul class="space-y-3 border-l border-slate-200 pl-4">
                                @foreach ($group['events'] as $event)
                                    <li class="relative text-sm">
                                        <span class="absolute -left-[1.3rem] top-1.5 size-2 rounded-full bg-navy"></span>
                                        <p class="tabular-nums text-xs text-slate-400">{{ $event['time'] }}</p>
                                        <p class="mt-0.5 font-medium text-black">{{ $event['description'] }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-platform.panel>
    </x-platform.partner-shell>
</x-app-layout>

<x-app-layout :title="$tenant->name . ' | InSyte CRM'">
    <x-platform.partner-shell :tenant="$tenant" :shell="$shell">
        @if (count($overview['attention']) > 0)
            <div class="mb-4 space-y-2">
                @foreach ($overview['attention'] as $item)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                        ⚠ {{ $item['message'] }}
                    </div>
                @endforeach
            </div>
        @endif

        <div class="grid gap-4 lg:grid-cols-2">
            <x-platform.panel :title="__('Account Snapshot')" compact>
                <dl class="space-y-3 text-sm">
                    @foreach ($overview['account'] as $row)
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-slate-500">{{ $row['label'] }}</dt>
                            <dd class="font-medium text-black">{{ $row['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-platform.panel>

            <x-platform.panel :title="__('Usage Snapshot')" compact>
                <dl class="space-y-3 text-sm">
                    @foreach ($overview['usage'] as $row)
                        <div>
                            <div class="mb-1.5 flex items-center justify-between gap-4">
                                <dt class="text-slate-500">{{ $row['label'] }}</dt>
                                <dd class="font-medium tabular-nums text-black">
                                    {{ $row['used_label'] }}@if ($row['limit_label']) / {{ $row['limit_label'] }}@elseif ($row['used_label'] !== '—') / —@endif
                                </dd>
                            </div>
                            <x-platform.usage-bar :percent="$row['percent']" />
                        </div>
                    @endforeach
                </dl>
            </x-platform.panel>

            <x-platform.panel :title="__('Integration Snapshot')" compact>
                <dl class="space-y-3 text-sm">
                    @foreach ($overview['integrations'] as $row)
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-slate-500">{{ $row['label'] }}</dt>
                            <dd @class([
                                'font-medium',
                                'text-emerald-700' => $row['status'] === 'connected',
                                'text-amber-700' => in_array($row['status'], ['failed', 'needs_attention'], true),
                                'text-slate-400' => $row['status'] === 'coming_soon',
                                'text-slate-600' => ! in_array($row['status'], ['connected', 'failed', 'needs_attention', 'coming_soon'], true),
                            ])>{{ $row['status_label'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-platform.panel>

            <x-platform.panel :title="__('Recent Activity')" compact>
                @if (count($overview['recent_activity']) === 0)
                    <p class="text-sm text-slate-500">{{ __('No recent activity yet.') }}</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($overview['recent_activity'] as $event)
                            <li class="flex gap-3 text-sm">
                                <span class="w-16 shrink-0 tabular-nums text-slate-400">{{ $event['time'] }}</span>
                                <span class="text-black">{{ $event['description'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-platform.panel>
        </div>
    </x-platform.partner-shell>
</x-app-layout>

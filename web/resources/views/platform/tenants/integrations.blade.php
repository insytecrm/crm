<x-app-layout :title="__('Integrations') . ' · ' . $tenant->name . ' | InSyte CRM'">
    <x-platform.partner-shell :tenant="$tenant" :shell="$shell">
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-black">{{ __('Integrations') }}</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ trans_choice(':count Connected|:count Connected', $integrations['connected_count'], ['count' => number_format($integrations['connected_count'])]) }}
                <span class="mx-1.5 text-slate-300">·</span>
                {{ trans_choice(':count Needs Attention|:count Needs Attention', $integrations['attention_count'], ['count' => number_format($integrations['attention_count'])]) }}
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($integrations['items'] as $item)
                <x-platform.panel compact>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-black">{{ $item['label'] }}</h3>
                            <p @class([
                                'mt-1 text-sm font-medium',
                                'text-emerald-700' => $item['status'] === 'connected',
                                'text-amber-700' => in_array($item['status'], ['failed', 'needs_attention'], true),
                                'text-slate-400' => $item['status'] === 'coming_soon',
                                'text-slate-500' => ! in_array($item['status'], ['connected', 'failed', 'needs_attention', 'coming_soon'], true),
                            ])>
                                ● {{ $item['status_label'] }}
                            </p>
                            @if ($item['last_sync_label'])
                                <p class="mt-1 text-xs text-slate-500">{{ __('Last sync') }}: {{ $item['last_sync_label'] }}</p>
                            @endif
                            @if ($item['attention_message'])
                                <p class="mt-2 text-sm font-medium text-amber-700">⚠ {{ $item['attention_message'] }}</p>
                            @endif
                        </div>
                        <x-ui.popover side="bottom" align="end" width="40" content-class="p-1" close-on-content-click>
                            <x-slot:trigger>
                                <button type="button" class="inline-flex size-8 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100" aria-label="{{ __('Actions') }}">
                                    <svg class="size-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="5" cy="12" r="1.6" />
                                        <circle cx="12" cy="12" r="1.6" />
                                        <circle cx="19" cy="12" r="1.6" />
                                    </svg>
                                </button>
                            </x-slot:trigger>
                            @foreach ($item['actions'] as $action)
                                <button type="button" disabled class="flex w-full cursor-not-allowed items-center rounded-md px-2 py-1.5 text-sm font-medium text-slate-400">
                                    {{ $action['label'] }}
                                </button>
                            @endforeach
                        </x-ui.popover>
                    </div>
                </x-platform.panel>
            @endforeach
        </div>
    </x-platform.partner-shell>
</x-app-layout>

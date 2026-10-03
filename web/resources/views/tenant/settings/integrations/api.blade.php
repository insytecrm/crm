<x-tenant-layout :title="__('Lead API') . ' | InSyte CRM'">
    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6">
        <a href="{{ route('tenant.settings.index', ['tab' => 'integrations']) }}" class="text-sm font-medium text-slate-500 hover:text-navy">
            ← {{ __('Back to Integrations') }}
        </a>
        <h1 class="mt-3 text-2xl font-semibold text-black">{{ __('Lead API') }}</h1>
        <p class="mt-1 text-sm text-slate-500">
            {{ __('Copy the URL and API key, then give them to a third-party developer so leads can be sent into this workspace.') }}
        </p>
    </div>

    <div class="space-y-4 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-6" x-data="{ copied: null }">
        <div>
            <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('API URL') }}</label>
            <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
                <input
                    type="text"
                    readonly
                    value="{{ $apiUrl }}"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-mono text-sm text-black"
                    data-testid="lead-api-url"
                >
                <x-ui.button
                    type="button"
                    variant="outline"
                    x-on:click="navigator.clipboard.writeText(@js($apiUrl)); copied = 'url'"
                >
                    <span x-text="copied === 'url' ? @js(__('Copied')) : @js(__('Copy'))"></span>
                </x-ui.button>
            </div>
        </div>

        <div>
            <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('API key') }}</label>
            <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
                <input
                    type="text"
                    readonly
                    value="{{ $apiKey }}"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-mono text-sm text-black"
                    data-testid="lead-api-key"
                >
                <x-ui.button
                    type="button"
                    variant="outline"
                    x-on:click="navigator.clipboard.writeText(@js($apiKey)); copied = 'key'"
                >
                    <span x-text="copied === 'key' ? @js(__('Copied')) : @js(__('Copy'))"></span>
                </x-ui.button>
            </div>
            @if ($tenant->lead_api_token_generated_at)
                <p class="mt-2 text-xs text-slate-400">
                    {{ __('Generated :date', ['date' => $tenant->lead_api_token_generated_at->timezone(config('app.timezone'))->format('M j, Y g:i A')]) }}
                </p>
            @endif
        </div>

        <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600">
            <p class="font-medium text-black">{{ __('How to authenticate') }}</p>
            <p class="mt-1">{{ __('Send the API key as a Bearer token in the Authorization header.') }}</p>
            <pre class="mt-3 overflow-x-auto rounded-lg bg-slate-900 px-3 py-2 font-mono text-xs text-slate-100">Authorization: Bearer {{ $apiKey }}</pre>
        </div>

        @if ($canManage)
            <form
                method="POST"
                action="{{ route('tenant.settings.integrations.api.regenerate') }}"
                onsubmit="return confirm(@js(__('Regenerating will invalidate the current API key immediately. Continue?')))"
            >
                @csrf
                <x-ui.button type="submit" variant="destructive">
                    {{ __('Regenerate API key') }}
                </x-ui.button>
            </form>
        @endif
    </div>
</x-tenant-layout>

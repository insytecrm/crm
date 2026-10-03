<x-tenant-layout :title="__('Automations') . ' | InSyte CRM'">
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-black">{{ __('Automations') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('Turn on ready-made playbooks or build custom workflows.') }}</p>
    </div>

    <div class="mb-8 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($playbooks as $playbook)
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm shadow-slate-200/40">
                <h2 class="text-sm font-semibold text-black">{{ $playbook['label'] }}</h2>
                <p class="mt-1 text-xs text-slate-500">{{ $playbook['description'] }}</p>
                @if ($canManage)
                    <div class="mt-4">
                        @if ($playbook['enabled'])
                            <form method="POST" action="{{ route('tenant.automations.playbooks.destroy', $playbook['key']) }}">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="outline" size="sm">{{ __('Disable') }}</x-ui.button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('tenant.automations.playbooks.store') }}">
                                @csrf
                                <input type="hidden" name="playbook" value="{{ $playbook['key'] }}">
                                <x-ui.button type="submit" variant="default" size="sm">{{ __('Enable') }}</x-ui.button>
                            </form>
                        @endif
                    </div>
                @else
                    <p class="mt-4 text-xs font-medium text-slate-400">
                        {{ $playbook['enabled'] ? __('Enabled') : __('Disabled') }}
                    </p>
                @endif
            </div>
        @endforeach
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-black">{{ __('Custom workflows') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('Build advanced automations with triggers, conditions, and actions.') }}</p>
        <div class="mt-4">
            <x-ui.button type="button" variant="default" :href="route('tenant.automations.workflows')">
                {{ __('Manage workflows') }}
            </x-ui.button>
        </div>
    </div>
</x-tenant-layout>

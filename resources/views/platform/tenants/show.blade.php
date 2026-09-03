<x-app-layout :title="$tenant->name . ' | InSyte CRM'">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-black">{{ $tenant->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Company details and tenant access') }}</p>
        </div>

        <x-ui.button variant="default" :href="route('tenants.edit', $tenant)">
            {{ __('Edit') }}
        </x-ui.button>
    </div>

    <x-auth-session-status class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <dl class="space-y-4 text-sm">
            <div class="flex flex-col gap-1 sm:flex-row sm:gap-4">
                <dt class="w-32 shrink-0 font-medium text-slate-500">{{ __('Slug') }}</dt>
                <dd class="text-black">{{ $tenant->id }}</dd>
            </div>
            <div class="flex flex-col gap-1 sm:flex-row sm:gap-4">
                <dt class="w-32 shrink-0 font-medium text-slate-500">{{ __('Email') }}</dt>
                <dd class="text-black">{{ $tenant->email ?: '—' }}</dd>
            </div>
            <div class="flex flex-col gap-1 sm:flex-row sm:gap-4">
                <dt class="w-32 shrink-0 font-medium text-slate-500">{{ __('Status') }}</dt>
                <dd class="capitalize text-black">{{ $tenant->status->value }}</dd>
            </div>
            <div class="flex flex-col gap-1 sm:flex-row sm:gap-4">
                <dt class="w-32 shrink-0 font-medium text-slate-500">{{ __('Database') }}</dt>
                <dd class="text-black">{{ $tenant->database()->getName() }}</dd>
            </div>
            <div class="flex flex-col gap-1 sm:flex-row sm:gap-4">
                <dt class="w-32 shrink-0 font-medium text-slate-500">{{ __('Login URL') }}</dt>
                <dd>
                    <a class="font-medium text-black hover:underline" href="{{ route('tenant.login', ['tenant' => $tenant->id]) }}">
                        {{ url('/'.$tenant->id.'/login') }}
                    </a>
                </dd>
            </div>
        </dl>
    </div>

    <form method="POST" action="{{ route('tenants.destroy', $tenant) }}" class="mt-6" onsubmit="return confirm('Delete this company and its database?');">
        @csrf
        @method('DELETE')
        <x-ui.button type="submit" variant="destructive">
            {{ __('Delete company') }}
        </x-ui.button>
    </form>
</x-app-layout>

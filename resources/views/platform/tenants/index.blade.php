<x-app-layout :title="__('Companies') . ' | InSyte CRM'">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-black">{{ __('Companies') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Manage tenant companies and their access') }}</p>
        </div>

        <x-ui.button variant="default" :href="route('tenants.create')">
            {{ __('Add company') }}
        </x-ui.button>
    </div>

    <x-auth-session-status class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
        <div class="p-6">
            @if ($tenants->isEmpty())
                <p class="text-slate-600">{{ __('No companies yet.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Name') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Slug') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Status') }}</th>
                                <th class="px-3 py-3 text-end text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($tenants as $tenant)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-3 py-4 whitespace-nowrap font-medium text-black">{{ $tenant->name }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-slate-600">{{ $tenant->id }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap capitalize text-slate-600">{{ $tenant->status->value }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-end space-x-3">
                                        <a class="text-sm font-medium text-black hover:underline" href="{{ route('tenants.show', $tenant) }}">{{ __('View') }}</a>
                                        <a class="text-sm font-medium text-black hover:underline" href="{{ route('tenants.edit', $tenant) }}">{{ __('Edit') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $tenants->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

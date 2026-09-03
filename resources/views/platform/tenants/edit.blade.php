<x-app-layout :title="__('Edit :name', ['name' => $tenant->name]) . ' | InSyte CRM'">
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-black">{{ __('Edit :name', ['name' => $tenant->name]) }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('Update company details and status') }}</p>
    </div>

    <div class="max-w-2xl rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('tenants.update', $tenant) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <x-input-label for="name" :value="__('Company name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $tenant->name)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="slug" :value="__('Slug')" />
                <x-text-input id="slug" type="text" class="mt-1 block w-full bg-slate-50" :value="$tenant->id" disabled />
            </div>

            <div>
                <x-input-label for="email" :value="__('Company email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $tenant->email)" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div>
                <x-input-label for="status" :value="__('Status')" />
                <x-ui.combobox
                    id="status"
                    name="status"
                    :options="collect($statuses)->map(fn ($status) => ['value' => $status->value, 'label' => ucfirst($status->value)])->all()"
                    :value="old('status', $tenant->status->value)"
                    :searchable="false"
                    required
                    class="mt-1"
                />
                <x-input-error class="mt-2" :messages="$errors->get('status')" />
            </div>

            <div class="flex items-center gap-4">
                <x-ui.button type="submit" variant="default">
                    {{ __('Save') }}
                </x-ui.button>
                <x-ui.button variant="link" :href="route('tenants.show', $tenant)">{{ __('Cancel') }}</x-ui.button>
            </div>
        </form>
    </div>
</x-app-layout>

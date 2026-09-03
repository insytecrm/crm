<x-app-layout :title="__('Add company') . ' | InSyte CRM'">
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-black">{{ __('Add company') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('Create a new tenant with its own database and login URL') }}</p>
    </div>

    <div class="max-w-2xl rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('tenants.store') }}" class="space-y-6">
            @csrf

            <div>
                <x-input-label for="name" :value="__('Company name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="slug" :value="__('Slug')" />
                <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug')" required />
                <p class="mt-1 text-sm text-slate-500">{{ __('Used in the login URL, e.g. /acme/login') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('slug')" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Company email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div>
                <x-input-label for="status" :value="__('Status')" />
                <x-ui.combobox
                    id="status"
                    name="status"
                    :options="collect($statuses)->map(fn ($status) => ['value' => $status->value, 'label' => ucfirst($status->value)])->all()"
                    :value="old('status', 'active')"
                    :searchable="false"
                    required
                    class="mt-1"
                />
                <x-input-error class="mt-2" :messages="$errors->get('status')" />
            </div>

            <div class="border-t border-slate-100 pt-6">
                <h2 class="text-lg font-semibold text-black">{{ __('First company user') }}</h2>
            </div>

            <div>
                <x-input-label for="admin_name" :value="__('Admin name')" />
                <x-text-input id="admin_name" name="admin_name" type="text" class="mt-1 block w-full" :value="old('admin_name')" required />
                <x-input-error class="mt-2" :messages="$errors->get('admin_name')" />
            </div>

            <div>
                <x-input-label for="admin_email" :value="__('Admin email')" />
                <x-text-input id="admin_email" name="admin_email" type="email" class="mt-1 block w-full" :value="old('admin_email')" required />
                <x-input-error class="mt-2" :messages="$errors->get('admin_email')" />
            </div>

            <div>
                <x-input-label for="admin_password" :value="__('Admin password')" />
                <x-text-input id="admin_password" name="admin_password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                <x-input-error class="mt-2" :messages="$errors->get('admin_password')" />
            </div>

            <div>
                <x-input-label for="admin_password_confirmation" :value="__('Confirm password')" />
                <x-text-input id="admin_password_confirmation" name="admin_password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
            </div>

            <div class="flex items-center gap-4">
                <x-ui.button type="submit" variant="default">
                    {{ __('Create company') }}
                </x-ui.button>
                <x-ui.button variant="link" :href="route('tenants.index')">{{ __('Cancel') }}</x-ui.button>
            </div>
        </form>
    </div>
</x-app-layout>

<x-modal :name="$modalName" maxWidth="lg">
    <div class="p-6">
        <h2 class="text-lg font-bold text-black">{{ $title }}</h2>

        <form method="POST" action="{{ $action }}" class="mt-4 space-y-4">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <div>
                <x-input-label for="{{ $modalName }}_name" :value="__('Full Name')" />
                <x-text-input
                    id="{{ $modalName }}_name"
                    name="name"
                    type="text"
                    class="mt-1 block w-full"
                    :value="old('name', $settingsUser?->name)"
                    required
                />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="{{ $modalName }}_email" :value="__('Email Address')" />
                <x-text-input
                    id="{{ $modalName }}_email"
                    name="email"
                    type="email"
                    class="mt-1 block w-full"
                    :value="old('email', $settingsUser?->email)"
                    required
                />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div>
                <x-input-label for="{{ $modalName }}_phone" :value="__('Phone Number')" />
                <x-text-input
                    id="{{ $modalName }}_phone"
                    name="phone"
                    type="tel"
                    class="mt-1 block w-full"
                    :value="old('phone', $settingsUser?->phone)"
                    autocomplete="tel"
                />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-input-label for="{{ $modalName }}_role" :value="__('Role')" />
                <x-ui.form-select
                    id="{{ $modalName }}_role"
                    name="role_id"
                    :value="(string) old('role_id', $settingsUser?->role_id)"
                    :options="collect($roles)->map(fn ($role) => ['value' => (string) $role->id, 'label' => $role->name])->all()"
                    :placeholder="__('Select role')"
                    required
                />
                <x-input-error class="mt-2" :messages="$errors->get('role_id')" />
            </div>

            <div>
                <x-input-label for="{{ $modalName }}_password" :value="$settingsUser ? __('New Password (optional)') : __('Password')" />
                <x-text-input
                    id="{{ $modalName }}_password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full"
                    :required="! $settingsUser"
                    autocomplete="new-password"
                />
                <x-input-error class="mt-2" :messages="$errors->get('password')" />
            </div>

            <div>
                <x-input-label for="{{ $modalName }}_password_confirmation" :value="__('Confirm Password')" />
                <x-text-input
                    id="{{ $modalName }}_password_confirmation"
                    name="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />
            </div>

            <div class="flex justify-end gap-2">
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', '{{ $modalName }}')">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button type="submit" variant="default">{{ $settingsUser ? __('Save User') : __('Create User') }}</x-ui.button>
            </div>
        </form>
    </div>
</x-modal>

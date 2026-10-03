@php
    use App\Enums\SettingsTab;
@endphp

<div class="max-w-xl">
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-black">{{ SettingsTab::Security->label() }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ SettingsTab::Security->description() }}</p>
    </div>

    <form method="POST" action="{{ route('tenant.settings.password.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="current_password" :value="__('Current Password')" />
            <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error class="mt-2" :messages="$errors->get('current_password')" />
        </div>

        <div>
            <x-input-label for="password" :value="__('New Password')" />
            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="flex items-center gap-3 pt-2">
            <x-ui.button type="submit" variant="default">{{ __('Update Password') }}</x-ui.button>
        </div>
    </form>
</div>

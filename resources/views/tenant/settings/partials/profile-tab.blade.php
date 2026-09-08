@php
    use App\Enums\SettingsTab;
@endphp

<div class="max-w-xl">
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-black">{{ SettingsTab::Profile->label() }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ SettingsTab::Profile->description() }}</p>
    </div>

    <form method="POST" action="{{ route('tenant.settings.profile.update') }}" class="space-y-4">
        @csrf
        @method('PATCH')

        <div>
            <x-input-label for="profile_name" :value="__('Full Name')" />
            <x-text-input id="profile_name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="profile_email" :value="__('Email Address')" />
            <x-text-input id="profile_email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="profile_phone" :value="__('Phone Number')" />
            <x-text-input id="profile_phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div class="flex items-center gap-3 pt-2">
            <x-ui.button type="submit" variant="default">{{ __('Save Profile') }}</x-ui.button>
        </div>
    </form>
</div>

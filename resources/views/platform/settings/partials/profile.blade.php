<div class="max-w-xl" id="settings-profile">
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-black">{{ __('Profile') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('Update your name and email for the platform account.') }}</p>
    </div>

    <form method="POST" action="{{ route('platform.settings.profile.update') }}" class="space-y-4">
        @csrf
        @method('PATCH')

        <div>
            <x-input-label for="profile_name" :value="__('Full Name')" />
            <x-text-input id="profile_name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="profile_email" :value="__('Email Address')" />
            <x-text-input id="profile_email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <x-ui.button type="submit" variant="default">{{ __('Save Profile') }}</x-ui.button>
    </form>
</div>

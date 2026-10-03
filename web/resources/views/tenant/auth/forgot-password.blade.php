<x-auth-layout :subtitle="__('Reset your password')">
    <div class="mb-4 text-sm text-slate-500">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <x-auth-session-status class="mb-4 text-sm font-medium text-emerald-600" :status="session('status')" />

    <form method="POST" action="{{ route('tenant.password.email') }}" class="space-y-4">
        @csrf

        <div>
            <x-auth.icon-input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                placeholder="{{ __('Email Address') }}"
            >
                <x-slot:icon>
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                </x-slot:icon>
            </x-auth.icon-input>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-ui.button type="submit" variant="default" class="w-full">
            {{ __('Email Password Reset Link') }}
        </x-ui.button>

        <div class="text-center">
            <x-ui.button variant="link" :href="route('tenant.login')">
                {{ __('Back to Sign In') }}
            </x-ui.button>
        </div>
    </form>
</x-auth-layout>

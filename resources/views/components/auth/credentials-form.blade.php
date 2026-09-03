@props([
    'action',
    'forgotPassword' => false,
])

<form method="POST" action="{{ $action }}" {{ $attributes->merge(['class' => 'space-y-4']) }}>
    @csrf

    <x-auth-session-status class="text-sm font-medium text-emerald-600" :status="session('status')" />

    <div>
        <x-auth.icon-input
            id="email"
            type="email"
            name="email"
            :value="old('email')"
            required
            autofocus
            autocomplete="username"
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

    <div x-data="{ show: false }">
        <x-auth.icon-input
            id="password"
            type="password"
            name="password"
            required
            autocomplete="current-password"
            placeholder="{{ __('Password') }}"
            x-bind:type="show ? 'text' : 'password'"
        >
            <x-slot:icon>
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </x-slot:icon>
            <x-slot:suffix>
                <button type="button" class="text-slate-400 hover:text-black" @click="show = ! show" :aria-label="show ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'">
                    <svg x-show="!show" class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <svg x-show="show" x-cloak class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </x-slot:suffix>
        </x-auth.icon-input>
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <label for="remember_me" class="inline-flex items-center gap-2">
        <input
            id="remember_me"
            type="checkbox"
            class="rounded border-slate-300 text-black shadow-sm focus:ring-navy"
            name="remember"
        >
        <span class="text-sm text-slate-500">{{ __('Remember Device') }}</span>
    </label>

    <x-ui.button type="submit" variant="default" class="w-full">
        {{ __('Sign In') }}
    </x-ui.button>

    @if ($forgotPassword)
        <div class="text-center">
            <x-ui.button variant="link" :href="route('password.request')">
                {{ __('Forgot your password?') }}
            </x-ui.button>
        </div>
    @endif
</form>

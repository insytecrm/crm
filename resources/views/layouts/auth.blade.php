<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ __('Sign in') }} | InSyte CRM</title>

        <x-favicon />

        <x-fonts />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-black">
        <div class="auth-page relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-8 sm:px-6">
            <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
                <div class="absolute -left-16 top-8 h-64 w-80 rotate-[18deg] rounded-3xl border-2 border-white/70"></div>
                <div class="absolute left-1/3 -top-10 h-72 w-96 -rotate-[12deg] rounded-3xl border-2 border-white/60"></div>
                <div class="absolute -right-10 bottom-10 h-80 w-[28rem] rotate-[8deg] rounded-3xl border-2 border-white/70"></div>
                <div class="absolute right-1/4 top-1/3 h-52 w-72 -rotate-[20deg] rounded-3xl border-2 border-white/50"></div>
            </div>

            <div class="relative grid w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-xl shadow-slate-300/40 lg:grid-cols-2 lg:items-stretch">
                <div class="flex flex-col justify-center px-8 py-10 sm:px-12 lg:px-14">
                    <x-auth.brand variant="light" />

                    <div class="mt-8">
                        <h1 class="text-3xl font-bold tracking-tight text-black">{{ __('Welcome Back') }}</h1>
                        <p class="mt-1 text-sm text-slate-500">{{ $subtitle ?? __('Access your dashboard') }}</p>
                    </div>

                    <div class="mt-6">
                        {{ $slot }}
                    </div>
                </div>

                <div class="hidden p-3 lg:block">
                    <img
                        src="{{ global_asset('images/login-hero.jpg') }}"
                        alt=""
                        class="h-full min-h-[36rem] w-full rounded-2xl object-cover object-center grayscale"
                    >
                </div>
            </div>
        </div>
    </body>
</html>

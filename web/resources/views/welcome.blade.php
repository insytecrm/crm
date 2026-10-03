<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="InSyte CRM — capture enquiries, follow up, run site visits, book deals, and track revenue for real estate channel partners.">

        <title>{{ $title ?? config('app.name') }}</title>

        <x-favicon />
        <x-fonts />

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-black antialiased">
        <div
            id="welcome-root"
            data-page="{{ $page ?? 'home' }}"
            data-slug="{{ $slug ?? '' }}"
            data-demo-url="{{ url('/landing/demo') }}"
            data-trial-url="{{ url('/landing/trial') }}"
            data-logo-light="{{ global_asset('images/2.png') }}"
            data-logo-dark="{{ global_asset('images/'.rawurlencode('inSyte (2).png')) }}"
            data-dashboard-src="{{ global_asset('images/hero-dashboard.png') }}"
        ></div>
    </body>
</html>

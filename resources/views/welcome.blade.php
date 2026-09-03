<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>InSyte CRM — Real Estate Channel Partner CRM | InSyte</title>
        <meta name="description" content="Capture leads from Facebook, property portals, and WhatsApp. Follow up smarter with InSyte AI OS. Track bookings, commission, and payouts — all in one place.">

        <x-favicon />

        <x-fonts />

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-black antialiased">
        <div
            id="landing-root"
            class="bg-white text-black"
            data-demo-url="{{ route('landing.demo.store') }}"
            data-trial-url="{{ route('landing.trial.store') }}"
        ></div>
    </body>
</html>

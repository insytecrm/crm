<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'InSyte' }}</title>
        <x-favicon />
        <x-fonts />
        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-white font-sans text-black antialiased">
        <header class="border-b border-black/5 bg-white">
            <div class="mx-auto flex h-16 max-w-4xl items-center justify-between px-4 sm:px-6">
                <a href="/" class="text-lg font-semibold text-black">InSyte</a>
                <div class="flex items-center gap-4 text-sm font-medium">
                    <a href="/contact" class="text-black/60 hover:text-black">Contact</a>
                    <a href="/" class="text-brand-accent hover:underline">Back to home</a>
                </div>
            </div>
        </header>
        <main class="mx-auto max-w-4xl px-4 py-12 sm:px-6">
            <h1 class="text-3xl font-bold tracking-tight text-black">{{ $heading }}</h1>
            <div class="prose prose-slate mt-8 max-w-none text-slate-600">
                {{ $slot }}
            </div>
        </main>
        <footer class="border-t border-black/5 py-8 text-center text-sm text-slate-500">
            © {{ date('Y') }} InSyte. All rights reserved.
        </footer>
    </body>
</html>

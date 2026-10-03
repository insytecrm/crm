@php
    $theme = $page['theme'];
    $fonts = $page['fonts'];
    $hero = $page['hero'];
    $buttons = $page['buttons'];
    $leadForm = $page['lead_form'];
    $ctaLabel = $page['cta_label'];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $page['project_name'] }}</title>
    <meta name="description" content="{{ $hero['subheadline'] ?? $page['project_name'] }}">
    <x-favicon />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="{{ $fonts['bunny_href'] }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/microsite.js'])
</head>
<body
    data-microsite
    @if (session('status') || $errors->any()) data-enquire-open="1" @endif
    class="antialiased"
    style="--ms-bg: {{ $theme['bg'] }}; --ms-surface: {{ $theme['surface'] }}; --ms-text: {{ $theme['text'] }}; --ms-muted: {{ $theme['muted'] }}; --ms-border: {{ $theme['border'] }}; --ms-accent: {{ $theme['accent'] }}; --ms-accent-text: {{ $theme['accent_text'] }}; --ms-font-body: {{ $fonts['primary_family'] }}; --ms-font-display: {{ $fonts['secondary_family'] }};"
>
    <header class="ms-header sticky top-0 z-40">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
            <a href="#top" class="flex min-w-0 items-center gap-3">
                @if ($page['logo_url'])
                    <img src="{{ $page['logo_url'] }}" alt="{{ $page['project_name'] }}" class="h-8 w-auto max-w-32 object-contain">
                @else
                    <span class="ms-display truncate text-lg font-semibold tracking-tight">{{ $page['project_name'] }}</span>
                @endif
            </a>
            <nav class="hidden items-center gap-6 text-sm md:flex">
                @foreach ($page['nav'] as $item)
                    <a href="#{{ $item['id'] }}" class="ms-muted transition hover:text-[var(--ms-text)]">{{ $item['label'] }}</a>
                @endforeach
            </nav>
            <button type="button" data-open-enquire class="ms-btn rounded-full px-4 py-2 text-xs font-semibold tracking-wide uppercase">
                {{ $buttons['header'] }}
            </button>
        </div>
    </header>

    <main id="top" class="pb-24 md:pb-0">
        <section data-hero class="relative min-h-[88vh] overflow-hidden">
            @if ($hero['video_embed'])
                <iframe src="{{ $hero['video_embed'] }}" title="{{ $page['project_name'] }}" class="ms-hero-media absolute inset-0 h-full w-full object-cover" allowfullscreen></iframe>
            @elseif ($hero['image_url'])
                <img src="{{ $hero['image_url'] }}" alt="{{ $page['project_name'] }}" class="ms-hero-media absolute inset-0 h-full w-full object-cover">
            @else
                <div class="ms-hero-media absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,var(--ms-accent)_0%,transparent_32%),linear-gradient(160deg,var(--ms-surface),var(--ms-bg))]"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/25 to-black/20"></div>
            <div class="relative mx-auto flex min-h-[88vh] max-w-6xl flex-col justify-end px-4 py-16 sm:px-6 sm:py-24">
                @if ($hero['kicker'])
                    <p class="text-xs font-semibold tracking-[0.28em] text-white/80 uppercase">{{ $hero['kicker'] }}</p>
                @endif
                <h1 class="ms-display mt-4 max-w-4xl text-5xl leading-none font-semibold text-white sm:text-7xl">{{ $hero['headline'] }}</h1>
                @if ($hero['subheadline'])
                    <p class="mt-5 max-w-xl text-base text-white/80 sm:text-lg">{{ $hero['subheadline'] }}</p>
                @endif
                <div class="mt-8 flex flex-wrap items-end gap-8">
                    @if ($hero['starting_price'])
                        <div>
                            <p class="text-[11px] tracking-[0.22em] text-white/60 uppercase">{{ __('Starting from') }}</p>
                            <p class="ms-display mt-1 text-3xl text-white">{{ $hero['starting_price'] }}</p>
                        </div>
                    @endif
                    @if ($hero['location'])
                        <div>
                            <p class="text-[11px] tracking-[0.22em] text-white/60 uppercase">{{ __('Location') }}</p>
                            <p class="mt-1 text-sm text-white/90">{{ $hero['location'] }}</p>
                        </div>
                    @endif
                    <button type="button" data-open-enquire class="ms-btn rounded-full px-6 py-3 text-sm font-semibold">
                        {{ $buttons['hero'] }}
                    </button>
                </div>
            </div>
        </section>

        @if ($page['highlights']['visible'])
            <section id="overview" class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24">
                <div data-reveal>
                    <p class="text-xs font-semibold tracking-[0.24em] text-[var(--ms-accent)] uppercase">{{ __('Overview') }}</p>
                    <h2 class="ms-display mt-3 text-4xl sm:text-5xl">{{ $page['highlights']['headline'] }}</h2>
                    @if ($page['highlights']['subheadline'])
                        <p class="ms-muted mt-3 max-w-2xl">{{ $page['highlights']['subheadline'] }}</p>
                    @endif
                </div>
                <div data-stagger class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    @foreach ($page['highlights']['items'] as $item)
                        <div class="ms-surface rounded-2xl p-5">
                            <p class="text-[11px] tracking-[0.18em] text-[var(--ms-muted)] uppercase">{{ $item['label'] }}</p>
                            <p class="ms-display mt-3 text-2xl">{{ $item['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($page['about']['visible'])
            <section class="mx-auto grid max-w-6xl gap-10 px-4 pb-16 sm:px-6 lg:grid-cols-2 lg:pb-24">
                <div data-reveal>
                    <h2 class="ms-display text-4xl sm:text-5xl">{{ $page['about']['headline'] }}</h2>
                    @if ($page['about']['subheadline'])
                        <p class="ms-muted mt-4 text-lg">{{ $page['about']['subheadline'] }}</p>
                    @endif
                    @if ($page['about']['body'])
                        <p class="mt-6 max-w-xl text-base leading-relaxed text-[var(--ms-text)]/90">{{ $page['about']['body'] }}</p>
                    @endif
                    <dl class="mt-8 grid gap-4 sm:grid-cols-2">
                        @foreach ($page['about']['facts'] as $fact)
                            <div>
                                <dt class="text-[11px] tracking-[0.18em] text-[var(--ms-muted)] uppercase">{{ $fact['label'] }}</dt>
                                <dd class="mt-1 text-sm">{{ $fact['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
                @if ($page['about']['image_url'])
                    <div data-reveal class="overflow-hidden rounded-3xl">
                        <img src="{{ $page['about']['image_url'] }}" alt="{{ $page['about']['headline'] }}" class="h-full min-h-80 w-full object-cover">
                    </div>
                @endif
            </section>
        @endif

        @if ($page['configurations']['visible'])
            <section id="configurations" class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24">
                <div data-reveal class="max-w-2xl">
                    <p class="text-xs font-semibold tracking-[0.24em] text-[var(--ms-accent)] uppercase">{{ __('Configurations') }}</p>
                    <h2 class="ms-display mt-3 text-4xl sm:text-5xl">{{ $page['configurations']['headline'] }}</h2>
                    @if ($page['configurations']['subheadline'])
                        <p class="ms-muted mt-4">{{ $page['configurations']['subheadline'] }}</p>
                    @endif
                </div>
                <div data-stagger class="mt-10 grid gap-5 md:grid-cols-2">
                    @foreach ($page['configurations']['groups'] as $group)
                        <article class="ms-surface overflow-hidden rounded-3xl p-6">
                            <h3 class="ms-display text-3xl">{{ $group['name'] }}</h3>
                            <div class="ms-muted mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm">
                                @if ($group['carpet_label'])
                                    <span>{{ $group['carpet_label'] }}</span>
                                @endif
                                @if ($group['price_label'])
                                    <span>{{ $group['price_label'] }}</span>
                                @endif
                                <span>{{ trans_choice(':count variant|:count variants', $group['variant_count'], ['count' => $group['variant_count']]) }}</span>
                            </div>
                            <div class="mt-6 flex flex-wrap gap-3">
                                <button type="button" data-open-enquire class="ms-btn rounded-full px-4 py-2 text-xs font-semibold uppercase">{{ $buttons['configurations'] }}</button>
                            </div>
                            @if ($group['variant_count'] > 1)
                                <details class="mt-5">
                                    <summary class="cursor-pointer text-sm text-[var(--ms-accent)]">{{ __('View details') }}</summary>
                                    <ul class="mt-4 space-y-3">
                                        @foreach ($group['variants'] as $variant)
                                            <li class="rounded-2xl border border-[var(--ms-border)] px-4 py-3 text-sm">
                                                <p class="font-medium">{{ $variant['name'] }}</p>
                                                <p class="ms-muted mt-1">
                                                    {{ collect([$variant['carpet_label'], $variant['price_label'], filled($variant['unit_count']) ? $variant['unit_count'].' '.__('units') : null])->filter()->implode(' · ') }}
                                                </p>
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($page['amenities']['visible'])
            <section id="amenities" class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24">
                <div data-reveal>
                    <p class="text-xs font-semibold tracking-[0.24em] text-[var(--ms-accent)] uppercase">{{ __('Lifestyle') }}</p>
                    <h2 class="ms-display mt-3 text-4xl sm:text-5xl">{{ $page['amenities']['headline'] }}</h2>
                    @if ($page['amenities']['subheadline'])
                        <p class="ms-muted mt-4 max-w-2xl">{{ $page['amenities']['subheadline'] }}</p>
                    @endif
                </div>
                <div data-stagger class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($page['amenities']['items'] as $amenity)
                        <div class="ms-surface rounded-2xl px-5 py-4 text-sm">{{ $amenity }}</div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($page['visuals']['visible'])
            <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24">
                <div data-reveal class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="ms-display text-4xl sm:text-5xl">{{ $page['visuals']['headline'] }}</h2>
                        @if ($page['visuals']['subheadline'])
                            <p class="ms-muted mt-4 max-w-xl">{{ $page['visuals']['subheadline'] }}</p>
                        @endif
                    </div>
                    @if ($page['visuals']['brochures'] !== [])
                        <a href="{{ $page['visuals']['brochures'][0]['url'] }}" class="ms-btn rounded-full px-5 py-2.5 text-sm font-semibold">{{ $buttons['visuals'] }}</a>
                    @endif
                </div>
                @if ($page['visuals']['video_embed'])
                    <div data-reveal class="mt-10 overflow-hidden rounded-3xl">
                        <iframe src="{{ $page['visuals']['video_embed'] }}" title="{{ $page['visuals']['headline'] }}" class="aspect-video w-full" allowfullscreen></iframe>
                    </div>
                @endif
                @if ($page['visuals']['gallery'] !== [])
                    <div data-stagger class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($page['visuals']['gallery'] as $image)
                            <img src="{{ $image['url'] }}" alt="{{ $image['name'] }}" class="h-64 w-full rounded-2xl object-cover">
                        @endforeach
                    </div>
                @endif
                @if ($page['visuals']['layouts'] !== [])
                    <div class="mt-8 flex flex-wrap gap-3">
                        @foreach ($page['visuals']['layouts'] as $file)
                            <a href="{{ $file['url'] }}" class="ms-btn-outline rounded-full px-4 py-2 text-sm">{{ $file['name'] }}</a>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        @if ($page['location']['visible'])
            <section id="location" class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24">
                <div data-reveal>
                    <p class="text-xs font-semibold tracking-[0.24em] text-[var(--ms-accent)] uppercase">{{ __('Location') }}</p>
                    <h2 class="ms-display mt-3 text-4xl sm:text-5xl">{{ $page['location']['headline'] }}</h2>
                    @if ($page['location']['subheadline'])
                        <p class="ms-muted mt-4">{{ $page['location']['subheadline'] }}</p>
                    @endif
                    @if ($page['location']['address'])
                        <p class="mt-3 text-sm">{{ $page['location']['address'] }}</p>
                    @endif
                </div>
                <div class="mt-8 grid gap-6 lg:grid-cols-2">
                    @if ($page['location']['map_embed'])
                        <div data-reveal class="overflow-hidden rounded-3xl">
                            <iframe src="{{ $page['location']['map_embed'] }}" title="{{ $page['location']['headline'] }}" class="h-80 w-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    @endif
                    @if ($page['location']['image_url'])
                        <img data-reveal src="{{ $page['location']['image_url'] }}" alt="{{ $page['location']['headline'] }}" class="h-80 w-full rounded-3xl object-cover">
                    @endif
                </div>
            </section>
        @endif

        @if ($page['developer']['visible'])
            <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                <div data-reveal class="ms-surface flex flex-col gap-6 rounded-3xl p-8 sm:flex-row sm:items-center">
                    @if ($page['developer']['image_url'])
                        <img src="{{ $page['developer']['image_url'] }}" alt="{{ $page['developer']['name'] }}" class="h-24 w-24 rounded-2xl object-cover">
                    @endif
                    <div>
                        <p class="text-[11px] tracking-[0.18em] text-[var(--ms-muted)] uppercase">{{ __('Developer') }}</p>
                        <h2 class="ms-display mt-2 text-3xl">{{ $page['developer']['headline'] }}</h2>
                        @if ($page['developer']['body'])
                            <p class="ms-muted mt-3 max-w-2xl">{{ $page['developer']['body'] }}</p>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        @if ($page['trust']['visible'])
            <section class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
                <div data-stagger class="grid gap-4 sm:grid-cols-3">
                    @foreach ($page['trust']['items'] as $item)
                        <div class="ms-surface rounded-2xl p-5 text-center">
                            <p class="text-[11px] tracking-[0.18em] text-[var(--ms-muted)] uppercase">{{ $item['label'] }}</p>
                            <p class="mt-2 text-sm font-medium">{{ $item['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section data-reveal class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24">
            <div class="ms-surface rounded-[2rem] px-6 py-12 text-center sm:px-12">
                <h2 class="ms-display text-4xl sm:text-5xl">{{ $page['cta']['headline'] }}</h2>
                @if ($page['cta']['subheadline'])
                    <p class="ms-muted mx-auto mt-4 max-w-xl">{{ $page['cta']['subheadline'] }}</p>
                @endif
                <button type="button" data-open-enquire class="ms-btn mt-8 rounded-full px-8 py-3 text-sm font-semibold">
                    {{ $buttons['cta'] }}
                </button>
            </div>
        </section>
    </main>

    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-[var(--ms-border)] bg-[var(--ms-bg)]/95 px-3 py-3 backdrop-blur md:hidden">
        <div class="flex gap-2">
            @if ($page['phone_link'])
                <a href="{{ $page['phone_link'] }}" class="ms-btn-outline flex-1 rounded-full py-2.5 text-center text-xs font-semibold">{{ __('Call') }}</a>
            @endif
            @if ($page['whatsapp_link'])
                <a href="{{ $page['whatsapp_link'] }}" class="ms-btn-outline flex-1 rounded-full py-2.5 text-center text-xs font-semibold">{{ __('WhatsApp') }}</a>
            @endif
            <button type="button" data-open-enquire class="ms-btn flex-1 rounded-full py-2.5 text-xs font-semibold">{{ $buttons['mobile'] }}</button>
        </div>
    </div>

    <dialog id="microsite-enquire" class="ms-dialog ms-surface w-[min(92vw,28rem)] rounded-3xl p-0 text-[var(--ms-text)]">
        <form method="POST" action="{{ $page['enquire_url'] }}" class="p-6">
            @csrf
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="ms-display text-3xl">{{ $leadForm['title'] }}</h2>
                    <p class="ms-muted mt-1 text-sm">{{ $leadForm['subtitle'] }}</p>
                </div>
                <button type="button" data-close-enquire class="ms-muted text-sm">{{ __('Close') }}</button>
            </div>
            @if (session('status'))
                <p class="mt-4 rounded-xl bg-[var(--ms-accent)]/15 px-3 py-2 text-sm text-[var(--ms-accent)]">{{ session('status') }}</p>
            @endif
            <div class="mt-5 space-y-3">
                @if ($leadForm['show_name'])
                    <label class="block text-xs tracking-[0.16em] uppercase">
                        {{ $leadForm['label_name'] }}
                        <input name="name" @if ($leadForm['require_name']) required @endif value="{{ old('name') }}" class="mt-1 h-11 w-full rounded-xl border border-[var(--ms-border)] bg-transparent px-3 text-sm">
                    </label>
                @endif
                @if ($leadForm['show_phone'])
                    <label class="block text-xs tracking-[0.16em] uppercase">
                        {{ $leadForm['label_phone'] }}
                        <input name="phone" @if ($leadForm['require_phone']) required @endif value="{{ old('phone') }}" class="mt-1 h-11 w-full rounded-xl border border-[var(--ms-border)] bg-transparent px-3 text-sm">
                    </label>
                @endif
                @if ($leadForm['show_email'])
                    <label class="block text-xs tracking-[0.16em] uppercase">
                        {{ $leadForm['label_email'] }}
                        <input name="email" type="email" @if ($leadForm['require_email']) required @endif value="{{ old('email') }}" class="mt-1 h-11 w-full rounded-xl border border-[var(--ms-border)] bg-transparent px-3 text-sm">
                    </label>
                @endif
                @if ($leadForm['show_configuration'] && $page['configurations']['visible'])
                    <label class="block text-xs tracking-[0.16em] uppercase">
                        {{ $leadForm['label_configuration'] }}
                        <select name="configuration" @if ($leadForm['require_configuration']) required @endif class="mt-1 h-11 w-full rounded-xl border border-[var(--ms-border)] bg-transparent px-3 text-sm">
                            <option value="">{{ __('Select') }}</option>
                            @foreach ($page['configurations']['groups'] as $group)
                                <option value="{{ $group['name'] }}" @selected(old('configuration') === $group['name'])>{{ $group['name'] }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
                <div class="hidden" aria-hidden="true">
                    <input name="website" tabindex="-1" autocomplete="off">
                </div>
                @if ($errors->any())
                    <p class="text-sm text-red-400">{{ $errors->first() }}</p>
                @endif
            </div>
            <button type="submit" class="ms-btn mt-5 w-full rounded-full py-3 text-sm font-semibold">{{ $leadForm['submit_label'] }}</button>
        </form>
    </dialog>
</body>
</html>

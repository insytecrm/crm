@if ($available)
    <a
        href="{{ $card['href'] }}"
        class="group flex flex-col rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:border-navy/30 hover:shadow"
    >
        <div class="flex items-start justify-between gap-3">
            <span class="inline-flex size-12 items-center justify-center rounded-xl {{ $card['icon_bg'] }}">
                @include('tenant.settings.partials.integration-logos', ['logo' => $card['logo']])
            </span>
            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $card['badge_classes'] }}">
                {{ $card['badge'] }}
            </span>
        </div>

        <h3 class="mt-4 text-base font-semibold text-black">{{ $card['name'] }}</h3>
        <p class="mt-1 flex-1 text-sm leading-relaxed text-slate-500">{{ $card['description'] }}</p>

        <span class="mt-5 inline-flex w-fit items-center gap-1.5 rounded-lg bg-navy px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition group-hover:bg-navy/90">
            {{ __('Configure') }}
            <span aria-hidden="true">→</span>
        </span>
    </a>
@else
    <div class="flex flex-col rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <span class="inline-flex size-12 items-center justify-center rounded-xl {{ $card['icon_bg'] }}">
                @include('tenant.settings.partials.integration-logos', ['logo' => $card['logo']])
            </span>
            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $card['badge_classes'] }}">
                {{ $card['badge'] }}
            </span>
        </div>

        <h3 class="mt-4 text-base font-semibold text-black">{{ $card['name'] }}</h3>
        <p class="mt-1 flex-1 text-sm leading-relaxed text-slate-500">{{ $card['description'] }}</p>

        <span class="mt-5 inline-flex w-fit items-center gap-1.5 rounded-lg bg-slate-100 px-3.5 py-2 text-sm font-semibold text-slate-500">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ __('Coming soon') }}
        </span>
    </div>
@endif

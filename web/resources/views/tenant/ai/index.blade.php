@php
    use App\Enums\AiConversationStatus;
    use App\Enums\AiOsTab;

    $aiAccents = [
        'sky' => [
            'card' => 'border-sky-100 bg-gradient-to-br from-sky-50 to-white hover:border-sky-200 hover:shadow-sky-100/70',
            'icon' => 'bg-sky-100 text-sky-600',
            'tile' => 'border-sky-100 bg-sky-50 text-sky-800 hover:border-sky-200 hover:bg-sky-100',
            'dot' => 'bg-sky-400',
            'wash' => 'from-sky-50/80 via-white to-white',
            'text' => 'text-sky-700',
        ],
        'emerald' => [
            'card' => 'border-emerald-100 bg-gradient-to-br from-emerald-50 to-white hover:border-emerald-200 hover:shadow-emerald-100/70',
            'icon' => 'bg-emerald-100 text-emerald-600',
            'tile' => 'border-emerald-100 bg-emerald-50 text-emerald-800 hover:border-emerald-200 hover:bg-emerald-100',
            'dot' => 'bg-emerald-400',
            'wash' => 'from-emerald-50/80 via-white to-white',
            'text' => 'text-emerald-700',
        ],
        'amber' => [
            'card' => 'border-amber-100 bg-gradient-to-br from-amber-50 to-white hover:border-amber-200 hover:shadow-amber-100/70',
            'icon' => 'bg-amber-100 text-amber-600',
            'tile' => 'border-amber-100 bg-amber-50 text-amber-800 hover:border-amber-200 hover:bg-amber-100',
            'dot' => 'bg-amber-400',
            'wash' => 'from-amber-50/80 via-white to-white',
            'text' => 'text-amber-700',
        ],
        'violet' => [
            'card' => 'border-violet-100 bg-gradient-to-br from-violet-50 to-white hover:border-violet-200 hover:shadow-violet-100/70',
            'icon' => 'bg-violet-100 text-violet-600',
            'tile' => 'border-violet-100 bg-violet-50 text-violet-800 hover:border-violet-200 hover:bg-violet-100',
            'dot' => 'bg-violet-400',
            'wash' => 'from-violet-50/80 via-white to-white',
            'text' => 'text-violet-700',
        ],
        'cyan' => [
            'card' => 'border-cyan-100 bg-gradient-to-br from-cyan-50 to-white hover:border-cyan-200 hover:shadow-cyan-100/70',
            'icon' => 'bg-cyan-100 text-cyan-600',
            'tile' => 'border-cyan-100 bg-cyan-50 text-cyan-800 hover:border-cyan-200 hover:bg-cyan-100',
            'dot' => 'bg-cyan-400',
            'wash' => 'from-cyan-50/80 via-white to-white',
            'text' => 'text-cyan-700',
        ],
        'rose' => [
            'card' => 'border-rose-100 bg-gradient-to-br from-rose-50 to-white hover:border-rose-200 hover:shadow-rose-100/70',
            'icon' => 'bg-rose-100 text-rose-600',
            'tile' => 'border-rose-100 bg-rose-50 text-rose-800 hover:border-rose-200 hover:bg-rose-100',
            'dot' => 'bg-rose-400',
            'wash' => 'from-rose-50/80 via-white to-white',
            'text' => 'text-rose-700',
        ],
    ];

    $suggestionAccents = ['rose', 'sky', 'amber', 'violet'];
@endphp

<x-tenant-layout :title="__('InSyte AI OS') . ' | InSyte CRM'">
    <div class="relative space-y-6">
        <div class="pointer-events-none absolute inset-x-0 -top-10 h-44 bg-gradient-to-r from-violet-100/70 via-sky-50/50 to-cyan-100/60 blur-2xl"></div>

        <div class="relative flex items-start gap-3">
            <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-brand-accent text-white shadow-sm shadow-violet-200/80">
                @include('tenant.ai.partials.glyph', ['icon' => 'sparkle', 'class' => 'size-6'])
            </div>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-bold tracking-tight text-black">{{ __('InSyte AI OS') }}</h1>
                    <span class="rounded-full bg-violet-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-violet-700">{{ __('Beta') }}</span>
                </div>
                <p class="mt-1 text-sm text-slate-500">{{ __('Your AI-powered real estate sales copilot. Write, plan, analyze and take action — all in one place.') }}</p>
            </div>
        </div>

        <x-ui.tabs default="chat" variant="line" class="relative">
            <x-ui.tabs.tab-list class="w-full">
                @foreach ($tabs as $aiTab)
                    @php
                        $tabAccent = $aiAccents[$aiTab->accent()] ?? $aiAccents['violet'];
                    @endphp
                    <x-ui.tabs.tab-trigger value="{{ $aiTab->value }}" class="gap-1.5">
                        <span class="{{ $tabAccent['text'] }}">
                            @include('tenant.ai.partials.glyph', ['icon' => $aiTab->icon(), 'class' => 'size-3.5'])
                        </span>
                        {{ $aiTab->label() }}
                    </x-ui.tabs.tab-trigger>
                @endforeach
            </x-ui.tabs.tab-list>

            <x-ui.tabs.tab-content value="{{ AiOsTab::Chat->value }}" default="chat" class="pt-5">
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
                    <div class="flex flex-col gap-6">
                        @include('tenant.ai.partials.chat-panel')

                        <section class="flex flex-col gap-3">
                            <h2 class="text-sm font-semibold text-black">{{ __('Try these examples') }}</h2>
                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach ($examples as $example)
                                    @php
                                        $exampleAccent = $aiAccents[$example['accent']] ?? $aiAccents['sky'];
                                    @endphp
                                    <button
                                        type="button"
                                        class="flex h-full flex-col gap-3 rounded-2xl border p-4 text-left shadow-sm shadow-slate-200/40 transition hover:shadow {{ $exampleAccent['card'] }}"
                                        @click="$dispatch('insyte-ai-example', @js($example['prompt']))"
                                    >
                                        <span class="flex size-9 items-center justify-center rounded-xl {{ $exampleAccent['icon'] }}">
                                            @include('tenant.ai.partials.glyph', ['icon' => $example['icon'], 'class' => 'size-[18px]'])
                                        </span>
                                        <span class="flex flex-col gap-1">
                                            <span class="text-sm font-semibold text-black">{{ $example['title'] }}</span>
                                            <span class="text-xs leading-relaxed text-slate-500">“{{ $example['prompt'] }}”</span>
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </section>

                        <section class="flex flex-col gap-3">
                            <h2 class="text-sm font-semibold text-black">{{ __('Recent AI conversations') }}</h2>
                            @if ($conversations->isEmpty())
                                <p class="rounded-2xl border border-dashed border-violet-200 bg-gradient-to-br from-violet-50/70 to-white px-4 py-6 text-sm text-slate-500">{{ __('No conversations yet. Ask InSyte AI OS to schedule a follow-up or find a lead.') }}</p>
                            @else
                                <div class="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm shadow-slate-200/40">
                                    @foreach ($conversations as $recent)
                                        <a
                                            href="{{ route('tenant.ai.index', ['conversation' => $recent->id]) }}"
                                            class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-violet-50/40"
                                        >
                                            <div class="flex min-w-0 items-center gap-3">
                                                <span class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-100 to-sky-100 text-violet-600">
                                                    @include('tenant.ai.partials.glyph', ['icon' => 'sparkle', 'class' => 'size-4'])
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-medium text-black">{{ $recent->title }}</p>
                                                    <p class="text-xs text-slate-400">{{ $recent->last_message_at?->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                            @if ($recent->status === AiConversationStatus::Completed)
                                                <span class="inline-flex shrink-0 items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                                    {{ __('Completed') }}
                                                </span>
                                            @else
                                                <span class="inline-flex shrink-0 items-center rounded-full bg-sky-50 px-2 py-0.5 text-[11px] font-semibold text-sky-700">
                                                    {{ __('Open') }}
                                                </span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    </div>

                    <aside class="flex flex-col gap-4">
                        <div class="rounded-2xl border border-rose-100 bg-gradient-to-br from-rose-50/80 to-white p-4 shadow-sm shadow-slate-200/40">
                            <h2 class="text-sm font-semibold text-black">{{ __("Today's suggestions") }}</h2>
                            <ul class="mt-3 flex flex-col gap-2">
                                @foreach ($suggestions as $index => $suggestion)
                                    @php
                                        $suggestionAccent = $aiAccents[$suggestionAccents[$index % count($suggestionAccents)]] ?? $aiAccents['sky'];
                                    @endphp
                                    <li>
                                        <a href="{{ $suggestion['href'] }}" class="flex items-center justify-between gap-2 rounded-xl border border-transparent bg-white/70 px-2.5 py-2 text-sm text-slate-600 shadow-sm shadow-slate-100/80 hover:border-slate-100 hover:text-black">
                                            <span class="flex min-w-0 items-center gap-2">
                                                <span class="size-2 shrink-0 rounded-full {{ $suggestionAccent['dot'] }}"></span>
                                                <span>{{ $suggestion['label'] }}</span>
                                            </span>
                                            <span aria-hidden="true" class="text-slate-300">→</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="rounded-2xl border border-sky-100 bg-gradient-to-br from-sky-50/70 to-white p-4 shadow-sm shadow-slate-200/40">
                            <h2 class="text-sm font-semibold text-black">{{ __('Quick actions') }}</h2>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                @foreach ($quickActions as $action)
                                    @php
                                        $actionAccent = $aiAccents[$action['accent']] ?? $aiAccents['sky'];
                                    @endphp
                                    <a href="{{ $action['href'] }}" class="flex flex-col items-center gap-2 rounded-xl border px-3 py-3 text-center text-xs font-semibold shadow-sm transition {{ $actionAccent['tile'] }}">
                                        <span class="flex size-8 items-center justify-center rounded-lg bg-white/80 {{ $actionAccent['text'] }}">
                                            @include('tenant.ai.partials.glyph', ['icon' => $action['icon'], 'class' => 'size-4'])
                                        </span>
                                        {{ $action['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 via-sky-50/70 to-cyan-50 p-4 shadow-sm shadow-slate-200/40">
                            <div class="flex items-center gap-2">
                                <span class="flex size-8 items-center justify-center rounded-lg bg-white/80 text-violet-600">
                                    @include('tenant.ai.partials.glyph', ['icon' => 'lightbulb', 'class' => 'size-4'])
                                </span>
                                <h2 class="text-sm font-semibold text-black">{{ __('AI productivity tip') }}</h2>
                            </div>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $tip }}</p>
                        </div>
                    </aside>
                </div>
            </x-ui.tabs.tab-content>

            @foreach ($tabs as $aiTab)
                @if (! $aiTab->isReady())
                    @php
                        $soonAccent = $aiAccents[$aiTab->accent()] ?? $aiAccents['violet'];
                    @endphp
                    <x-ui.tabs.tab-content value="{{ $aiTab->value }}" default="chat" class="pt-5">
                        <div class="rounded-2xl border bg-gradient-to-br p-8 shadow-sm shadow-slate-200/40 {{ $soonAccent['card'] }} {{ $soonAccent['wash'] }}">
                            <span class="flex size-12 items-center justify-center rounded-2xl {{ $soonAccent['icon'] }}">
                                @include('tenant.ai.partials.glyph', ['icon' => $aiTab->icon(), 'class' => 'size-6'])
                            </span>
                            <h2 class="mt-4 text-lg font-semibold text-black">{{ $aiTab->label() }}</h2>
                            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-500">{{ $aiTab->comingSoonDescription() }}</p>
                            <p class="mt-4 inline-flex rounded-full bg-white/80 px-2.5 py-1 text-xs font-semibold uppercase tracking-wider {{ $soonAccent['text'] }}">{{ __('Coming soon') }}</p>
                        </div>
                    </x-ui.tabs.tab-content>
                @endif
            @endforeach
        </x-ui.tabs>
    </div>
</x-tenant-layout>

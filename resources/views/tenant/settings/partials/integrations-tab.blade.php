@php
    use App\Enums\PropertyPortal;
    use App\Enums\SettingsTab;
    use App\Support\Platform\TenantPlanAccess;

    $leadCaptureCards = [
        [
            'key' => 'api',
            'name' => __('API'),
            'description' => __('Share a URL and API key with third-party systems to receive leads.'),
            'badge' => __('Popular'),
            'badge_classes' => 'bg-sky-50 text-sky-700 ring-sky-100',
            'icon_bg' => 'bg-slate-900',
            'href' => route('tenant.settings.integrations.api'),
            'logo' => 'api',
            'capability' => 'integration.api',
        ],
        [
            'key' => 'google-sheets',
            'name' => __('Google Sheets'),
            'description' => __('Connect Google Sheets to automatically capture and sync leads.'),
            'badge' => __('Popular'),
            'badge_classes' => 'bg-sky-50 text-sky-700 ring-sky-100',
            'icon_bg' => 'bg-emerald-50',
            'href' => route('tenant.settings.integrations.google-sheets.index'),
            'logo' => 'google-sheets',
            'capability' => 'integration.google_sheets',
        ],
        [
            'key' => 'facebook',
            'name' => __('Facebook'),
            'description' => __('Receive leads from one Facebook Page’s Lead Ads forms.'),
            'badge' => __('Popular'),
            'badge_classes' => 'bg-sky-50 text-sky-700 ring-sky-100',
            'icon_bg' => 'bg-blue-50',
            'href' => route('tenant.settings.integrations.facebook.show'),
            'logo' => 'facebook',
            'capability' => 'integration.facebook',
        ],
    ];

    $portalCards = [
        [
            'key' => PropertyPortal::NinetyNineAcres->value,
            'name' => PropertyPortal::NinetyNineAcres->label(),
            'description' => __('Import property inquiries from 99acres listings.'),
            'badge' => __('Real Estate'),
            'badge_classes' => 'bg-violet-50 text-violet-700 ring-violet-100',
            'icon_bg' => 'bg-blue-50',
            'href' => route('tenant.settings.integrations.portal', ['portal' => PropertyPortal::NinetyNineAcres->value]),
            'logo' => '99acres',
            'capability' => 'integration.99acres',
        ],
        [
            'key' => PropertyPortal::Housing->value,
            'name' => PropertyPortal::Housing->label(),
            'description' => __('Sync Housing.com leads into your CRM pipeline.'),
            'badge' => __('Real Estate'),
            'badge_classes' => 'bg-violet-50 text-violet-700 ring-violet-100',
            'icon_bg' => 'bg-amber-50',
            'href' => route('tenant.settings.integrations.portal', ['portal' => PropertyPortal::Housing->value]),
            'logo' => 'housing',
            'capability' => 'integration.housing',
        ],
        [
            'key' => PropertyPortal::MagicBricks->value,
            'name' => PropertyPortal::MagicBricks->label(),
            'description' => __('Capture MagicBricks inquiries automatically.'),
            'badge' => __('Real Estate'),
            'badge_classes' => 'bg-violet-50 text-violet-700 ring-violet-100',
            'icon_bg' => 'bg-red-50',
            'href' => route('tenant.settings.integrations.portal', ['portal' => PropertyPortal::MagicBricks->value]),
            'logo' => 'magicbricks',
            'capability' => 'integration.magicbricks',
        ],
        [
            'key' => PropertyPortal::NoBroker->value,
            'name' => PropertyPortal::NoBroker->label(),
            'description' => __('Receive NoBroker property leads in real time.'),
            'badge' => __('Real Estate'),
            'badge_classes' => 'bg-violet-50 text-violet-700 ring-violet-100',
            'icon_bg' => 'bg-pink-50',
            'href' => route('tenant.settings.integrations.portal', ['portal' => PropertyPortal::NoBroker->value]),
            'logo' => 'nobroker',
            'capability' => 'integration.nobroker',
        ],
    ];

    $comingSoonCards = [
        [
            'name' => __('WhatsApp'),
            'description' => __('Send and track WhatsApp messages from leads.'),
            'badge' => __('Communication'),
            'badge_classes' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
            'icon_bg' => 'bg-emerald-50',
            'logo' => 'whatsapp',
        ],
        [
            'name' => __('Email'),
            'description' => __('Sync email conversations with lead records.'),
            'badge' => __('Communication'),
            'badge_classes' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
            'icon_bg' => 'bg-red-50',
            'logo' => 'gmail',
        ],
        [
            'name' => __('Calendar'),
            'description' => __('Sync follow-ups and site visits with your calendar.'),
            'badge' => __('Productivity'),
            'badge_classes' => 'bg-orange-50 text-orange-700 ring-orange-100',
            'icon_bg' => 'bg-blue-50',
            'logo' => 'calendar',
        ],
    ];

    $planAccess = app(TenantPlanAccess::class);
    $visibleLeadCaptureCards = array_values(array_filter(
        $leadCaptureCards,
        fn (array $card): bool => $planAccess->hasCapability($card['capability']),
    ));
    $visiblePortalCards = array_values(array_filter(
        $portalCards,
        fn (array $card): bool => $planAccess->hasCapability($card['capability']),
    ));
@endphp

<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <h2 class="text-2xl font-semibold text-black">{{ __('Connect Your Tools') }}</h2>
        <p class="mt-1 max-w-2xl text-sm text-slate-500">
            {{ __('Integrate your favorite apps to capture leads, sync data, and automate your workflow.') }}
        </p>
    </div>

    <div class="inline-flex max-w-sm items-start gap-2 rounded-xl border border-amber-100 bg-amber-50 px-3 py-2 text-sm text-amber-800">
        <svg class="mt-0.5 size-4 shrink-0 text-amber-500" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path fill-rule="evenodd" d="M14.615 1.595a.75.75 0 0 1 .359.852L12.982 9.75h7.268a.75.75 0 0 1 .548 1.262l-10.5 11.25a.75.75 0 0 1-1.272-.71l1.992-7.302H3.75a.75.75 0 0 1-.548-1.262l10.5-11.25a.75.75 0 0 1 .913-.143Z" clip-rule="evenodd" />
        </svg>
        <span>{{ __('More integrations coming soon. We’re constantly adding new apps.') }}</span>
    </div>
</div>

{{-- Keep SettingsTab label discoverable for existing navigation assertions --}}
<span class="sr-only">{{ SettingsTab::Integrations->label() }}</span>

@if ($visibleLeadCaptureCards !== [])
    <section class="mt-8 space-y-4">
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">{{ __('Lead capture') }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ __('Bring leads in from APIs, spreadsheets, and ads.') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($visibleLeadCaptureCards as $card)
                @include('tenant.settings.partials.integration-card', ['card' => $card, 'available' => true])
            @endforeach
        </div>
    </section>
@endif

@if ($visiblePortalCards !== [])
    <section class="mt-8 space-y-4">
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">{{ __('Property portals') }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ __('Import inquiries from listing portals.') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($visiblePortalCards as $card)
                @include('tenant.settings.partials.integration-card', ['card' => $card, 'available' => true])
            @endforeach
        </div>
    </section>
@endif

<section class="mt-8 space-y-4">
    <div>
        <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">{{ __('Coming soon') }}</h3>
        <p class="mt-1 text-sm text-slate-500">{{ __('Communication and productivity tools on the roadmap.') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($comingSoonCards as $card)
            @include('tenant.settings.partials.integration-card', ['card' => $card, 'available' => false])
        @endforeach
    </div>
</section>

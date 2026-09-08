<div class="flex items-center gap-1">
    <div x-data="{ open: false }" class="relative">
        <button
            type="button"
            class="inline-flex size-8 items-center justify-center rounded-md text-slate-600 hover:bg-slate-100"
            @click="open = ! open"
            aria-label="{{ __('Quick add') }}"
            :aria-expanded="open"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </button>

        <div
            x-show="open"
            x-transition
            x-cloak
            @click.outside="open = false"
            class="absolute right-0 z-50 mt-2 w-48 overflow-hidden rounded-2xl border border-slate-200 bg-white py-1 shadow-sm shadow-slate-900/5"
        >
            <a
                href="{{ route('tenant.leads.index', ['add' => 1]) }}"
                class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
                @click="open = false"
            >
                {{ __('Add Lead') }}
            </a>
            <x-tenant.can :feature="\App\Enums\PlanFeature::Bookings->value">
                <a
                    href="{{ route('tenant.bookings.create') }}"
                    class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
                    @click="open = false"
                >
                    {{ __('Add Booking') }}
                </a>
            </x-tenant.can>
            <x-tenant.can :feature="\App\Enums\PlanFeature::Properties->value">
                <a
                    href="{{ route('tenant.properties.create') }}"
                    class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
                    @click="open = false"
                >
                    {{ __('Add Property') }}
                </a>
            </x-tenant.can>
        </div>
    </div>

    <x-tenant.can :feature="\App\Enums\PlanFeature::TeamInbox->value">
        <button
            type="button"
            class="inline-flex size-8 items-center justify-center rounded-md text-slate-600 hover:bg-slate-100"
            @click="$dispatch('open-drawer', 'team-inbox')"
            aria-label="{{ __('Team inbox') }}"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 0 2.012-1.243l.256-.512a2.25 2.25 0 0 1 2.013-1.243h3.218a2.25 2.25 0 0 1 2.013 1.243l.256.512a2.25 2.25 0 0 0 2.013 1.243H21.75M2.25 13.5V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.5M2.25 13.5l1.086-5.484A2.25 2.25 0 0 1 5.47 6.09h13.06a2.25 2.25 0 0 1 2.134 1.926L21.75 13.5" />
            </svg>
        </button>
    </x-tenant.can>
</div>

<div
    id="team-inbox-drawer-root"
    data-bootstrap-url="{{ route('tenant.team-chat.bootstrap') }}"
    data-store-url="{{ route('tenant.team-chat.messages.store') }}"
    data-messages-url-template="{{ route('tenant.team-chat.messages', ['conversation' => '__ID__']) }}"
    data-current-user-id="{{ auth()->id() }}"
></div>

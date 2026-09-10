@php
    $activityNotificationConfig = [
        'indexUrl' => route('tenant.activity-notifications.index'),
        'dismissUrl' => route('tenant.activity-notifications.dismiss-popup'),
        'readAllUrl' => route('tenant.activity-notifications.read-all'),
        'readUrlTemplate' => route('tenant.activity-notifications.read', ['notification' => '__ID__']),
        'callStoreUrlTemplate' => route('tenant.leads.activities.store', ['lead' => '__ID__']),
        'csrfToken' => csrf_token(),
        'pollMs' => 30000,
    ];
@endphp

<div
    class="flex items-center gap-1"
    x-data="activityDueNotifications(@js($activityNotificationConfig))"
>
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

    <div class="relative">
        <button
            type="button"
            class="relative inline-flex size-8 items-center justify-center rounded-md text-slate-600 hover:bg-slate-100"
            @click="open = ! open"
            aria-label="{{ __('Notifications') }}"
            :aria-expanded="open"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>
            <span
                x-show="unreadCount > 0"
                x-cloak
                class="absolute -right-0.5 -top-0.5 inline-flex min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold leading-4 text-white"
                x-text="unreadCount > 9 ? '9+' : unreadCount"
            ></span>
        </button>

        <div
            x-show="open"
            x-transition
            x-cloak
            @click.outside="open = false"
            class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5"
        >
            <div class="flex items-center justify-between border-b border-slate-100 px-3 py-2.5">
                <p class="text-sm font-semibold text-slate-900">{{ __('Notifications') }}</p>
                <button
                    type="button"
                    class="text-xs font-medium text-slate-500 hover:text-slate-800 disabled:opacity-40"
                    @click="markAllRead()"
                    :disabled="unreadCount === 0"
                >
                    {{ __('Mark all read') }}
                </button>
            </div>

            <template x-if="notifications.length === 0">
                <p class="px-3 py-8 text-center text-sm text-slate-500">{{ __('No notifications yet') }}</p>
            </template>

            <div x-show="notifications.length > 0" class="max-h-80 overflow-y-auto">
                <template x-for="(notification, index) in notifications" :key="notification.id">
                    <button
                        type="button"
                        class="flex w-full items-start gap-2 border-b border-slate-50 px-3 py-2.5 text-left hover:bg-slate-50"
                        :class="index < 3 ? 'bg-slate-50/70' : ''"
                        @click="openNotification(notification)"
                    >
                        <span
                            class="mt-1.5 size-2 shrink-0 rounded-full"
                            :class="notification.read_at ? 'bg-transparent' : 'bg-sky-500'"
                        ></span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium text-slate-900" x-text="notification.title"></span>
                            <span class="mt-0.5 block truncate text-xs text-slate-600" x-text="notification.body"></span>
                            <span class="mt-1 block text-[11px] text-slate-400" x-text="formatTime(notification.scheduled_at || notification.created_at)"></span>
                        </span>
                    </button>
                </template>
            </div>
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

    <div
        x-show="activePopup"
        x-cloak
        class="pointer-events-none fixed inset-x-0 bottom-4 z-[70] flex justify-end px-4 sm:bottom-6 sm:px-6"
    >
        <div
            x-show="activePopup"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-2 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="translate-y-2 opacity-0"
            class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg shadow-slate-900/10"
            role="dialog"
            aria-modal="false"
            aria-label="{{ __('Activity due') }}"
        >
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 px-4 py-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wide text-sky-700" x-text="activePopup?.type_label || kindLabel(activePopup?.kind)"></p>
                    <p class="mt-0.5 truncate text-sm font-semibold text-slate-900" x-text="activePopup?.label"></p>
                    <p class="mt-0.5 text-xs text-slate-500" x-text="formatTime(activePopup?.due_at)"></p>
                </div>
                <button
                    type="button"
                    class="inline-flex size-7 shrink-0 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                    @click="dismissActivePopup()"
                    aria-label="{{ __('Close') }}"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-4 py-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                    <p class="text-sm font-semibold text-slate-900" x-text="activePopup?.lead?.name || '{{ __('No lead') }}'"></p>
                    <p class="mt-1 text-xs text-slate-600">
                        <span x-show="activePopup?.lead?.phone" x-text="activePopup?.lead?.phone"></span>
                        <span x-show="activePopup?.lead?.phone && activePopup?.lead?.status"> · </span>
                        <span x-show="activePopup?.lead?.status" x-text="activePopup?.lead?.status"></span>
                    </p>
                    <p class="mt-2 text-xs text-slate-500" x-show="activePopup?.notes" x-text="activePopup?.notes"></p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-1.5 border-t border-slate-100 px-3 py-2.5">
                <button
                    type="button"
                    class="inline-flex items-center rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100 disabled:opacity-40"
                    @click="callFromPopup()"
                    :disabled="! activePopup?.lead?.call_url"
                >
                    {{ __('Call') }}
                </button>
                <button
                    type="button"
                    class="inline-flex items-center rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100 disabled:opacity-40"
                    @click="openWhatsAppFromPopup()"
                    :disabled="! activePopup?.lead?.can_whatsapp"
                >
                    {{ __('WhatsApp') }}
                </button>
                <button
                    type="button"
                    class="inline-flex items-center rounded-md bg-navy px-2.5 py-1.5 text-xs font-medium text-white hover:bg-navy/90"
                    @click="openLeadFromPopup(); dismissActivePopup()"
                >
                    {{ __('Open lead') }}
                </button>
            </div>
        </div>
    </div>
</div>

<div
    id="team-inbox-drawer-root"
    data-bootstrap-url="{{ route('tenant.team-chat.bootstrap') }}"
    data-store-url="{{ route('tenant.team-chat.messages.store') }}"
    data-messages-url-template="{{ route('tenant.team-chat.messages', ['conversation' => '__ID__']) }}"
    data-current-user-id="{{ auth()->id() }}"
></div>

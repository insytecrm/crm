@php
    use App\Enums\SettingsTab;

    $notificationItems = [
        [
            'title' => __('Follow-up reminders'),
            'description' => __('Get notified when a follow-up is due or overdue.'),
        ],
        [
            'title' => __('Site visit reminders'),
            'description' => __('Alerts before scheduled site visits.'),
        ],
        [
            'title' => __('Task assignments'),
            'description' => __('Email when a task is assigned to you.'),
        ],
        [
            'title' => __('Weekly summary'),
            'description' => __('A digest of leads, activities, and bookings.'),
        ],
    ];
@endphp

<div class="max-w-2xl">
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-black">{{ SettingsTab::Notifications->label() }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ SettingsTab::Notifications->description() }}</p>
    </div>

    <div class="space-y-3">
        @foreach ($notificationItems as $item)
            <div class="flex items-start justify-between gap-4 rounded-xl border border-slate-100 bg-slate-50/60 px-4 py-3">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-black">{{ $item['title'] }}</p>
                    <p class="mt-0.5 text-sm text-slate-500">{{ $item['description'] }}</p>
                </div>
                <span class="shrink-0 rounded-full bg-white px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400 ring-1 ring-slate-200">
                    {{ __('Soon') }}
                </span>
            </div>
        @endforeach
    </div>

    <p class="mt-4 text-sm text-slate-500">{{ __('Notification preferences will be available in a future update.') }}</p>
</div>

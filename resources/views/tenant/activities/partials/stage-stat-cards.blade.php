@props([
    'stage',
    'stages',
    'statistics',
    'indexRoute',
    'search' => '',
])

<div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 sm:items-stretch">
    @foreach ($stages as $stageOption)
        <x-tenant.stat-card
            :label="$stageOption->label()"
            :value="$statistics[$stageOption->value]"
            :accent="$stageOption->accent()"
            :href="route($indexRoute, array_filter(['stage' => $stageOption->value, 'search' => $search ?: null]))"
            :active="$stage === $stageOption"
        >
            <x-slot:icon>
                @if ($stageOption === \App\Enums\ScheduledActivityStage::Pending)
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                @elseif ($stageOption === \App\Enums\ScheduledActivityStage::Overdue)
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                @elseif ($stageOption === \App\Enums\ScheduledActivityStage::Rescheduled)
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 4.5 19 2" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 4.5 5 2" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l2.25 2.25" />
                    </svg>
                @else
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                @endif
            </x-slot:icon>
        </x-tenant.stat-card>
    @endforeach
</div>

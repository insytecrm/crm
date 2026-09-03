<x-tenant-layout :title="__('Integrations') . ' | InSyte CRM'">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            ['name' => __('WhatsApp'), 'description' => __('Send and track WhatsApp messages from leads.')],
            ['name' => __('Email'), 'description' => __('Sync email conversations with lead records.')],
            ['name' => __('Calendar'), 'description' => __('Sync follow-ups and site visits with your calendar.')],
        ] as $integration)
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-black">{{ $integration['name'] }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $integration['description'] }}</p>
                <p class="mt-3 text-xs font-medium uppercase tracking-wider text-slate-400">{{ __('Coming soon') }}</p>
            </div>
        @endforeach
    </div>
</x-tenant-layout>

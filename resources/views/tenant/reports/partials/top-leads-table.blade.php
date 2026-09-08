@props([
    'leads',
    'emptyMessage' => null,
])

<div class="overflow-x-auto px-5 pb-4 pt-1 sm:px-6 sm:pb-5">
    <table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50/80">
            <tr class="align-middle">
                <th class="whitespace-nowrap px-3 py-2.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Rank') }}</th>
                <th class="min-w-0 px-3 py-2.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Lead') }}</th>
                <th class="whitespace-nowrap px-3 py-2.5 text-end align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Lead Score') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
            @forelse ($leads as $index => $lead)
                <tr class="transition-colors hover:bg-slate-50/70">
                    <td class="whitespace-nowrap px-3 py-2 align-middle text-sm font-semibold tabular-nums text-slate-500">
                        {{ $index + 1 }}
                    </td>
                    <td class="min-w-0 px-3 py-2 align-middle">
                        <x-tenant.lead-link :lead="$lead" />
                    </td>
                    <td class="whitespace-nowrap px-3 py-2 align-middle text-end text-sm font-semibold tabular-nums text-navy">
                        {{ number_format((int) $lead->lead_score) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-3 py-10 text-center text-sm text-slate-500">
                        {{ $emptyMessage ?? __('No leads for this period.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

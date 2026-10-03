@props([
    'rows',
    'emptyMessage' => null,
])

<div class="overflow-x-auto px-5 pb-4 pt-1 sm:px-6 sm:pb-5">
    <table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50/80">
            <tr class="align-middle">
                <th class="whitespace-nowrap px-4 py-2.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Source') }}</th>
                <th class="whitespace-nowrap px-4 py-2.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Total Leads') }}</th>
                <th class="whitespace-nowrap px-4 py-2.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Active') }}</th>
                <th class="whitespace-nowrap px-4 py-2.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Converted') }}</th>
                <th class="whitespace-nowrap px-4 py-2.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Lost') }}</th>
                <th class="whitespace-nowrap px-4 py-2.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Conversion Rate') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
            @forelse ($rows as $row)
                <tr class="transition-colors hover:bg-slate-50/70">
                    <td class="whitespace-nowrap px-4 py-2 align-middle text-sm font-medium text-black">{{ $row['source'] }}</td>
                    <td class="whitespace-nowrap px-4 py-2 align-middle text-sm tabular-nums text-slate-700">{{ number_format($row['total']) }}</td>
                    <td class="whitespace-nowrap px-4 py-2 align-middle text-sm font-medium tabular-nums text-sky-700">{{ number_format($row['active']) }}</td>
                    <td class="whitespace-nowrap px-4 py-2 align-middle text-sm font-medium tabular-nums text-emerald-700">{{ number_format($row['converted']) }}</td>
                    <td class="whitespace-nowrap px-4 py-2 align-middle text-sm font-medium tabular-nums text-rose-700">{{ number_format($row['lost']) }}</td>
                    <td class="whitespace-nowrap px-4 py-2 align-middle text-sm font-semibold tabular-nums text-navy">{{ number_format($row['conversion_rate'], 1) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">
                        {{ $emptyMessage ?? __('No source performance data for this period.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

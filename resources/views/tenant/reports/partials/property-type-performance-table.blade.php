@props([
    'rows',
    'emptyMessage' => null,
])

<div class="overflow-x-auto px-5 pb-4 pt-1 sm:px-6 sm:pb-5">
    <table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50/80">
            <tr class="align-middle">
                <th class="whitespace-nowrap px-4 py-2.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Property Type') }}</th>
                <th class="whitespace-nowrap px-4 py-2.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Total') }}</th>
                <th class="whitespace-nowrap px-4 py-2.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Active') }}</th>
                <th class="whitespace-nowrap px-4 py-2.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Converted') }}</th>
                <th class="whitespace-nowrap px-4 py-2.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Lost') }}</th>
                <th class="whitespace-nowrap px-4 py-2.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Conversion Rate') }}</th>
                <th class="whitespace-nowrap px-4 py-2.5 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
            @forelse ($rows as $row)
                @php
                    $statusClass = match ($row['status']) {
                        'need_work' => 'bg-rose-50 text-rose-700 ring-rose-600/10',
                        'average' => 'bg-amber-50 text-amber-700 ring-amber-600/10',
                        'good' => 'bg-sky-50 text-sky-700 ring-sky-600/10',
                        'excellent' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10',
                        default => 'bg-slate-50 text-slate-600 ring-slate-500/10',
                    };
                @endphp
                <tr class="transition-colors hover:bg-slate-50/70">
                    <td class="whitespace-nowrap px-4 py-2 align-middle text-sm font-medium text-black">{{ $row['property_type'] }}</td>
                    <td class="whitespace-nowrap px-4 py-2 align-middle text-sm tabular-nums text-slate-700">{{ number_format($row['total']) }}</td>
                    <td class="whitespace-nowrap px-4 py-2 align-middle text-sm font-medium tabular-nums text-sky-700">{{ number_format($row['active']) }}</td>
                    <td class="whitespace-nowrap px-4 py-2 align-middle text-sm font-medium tabular-nums text-emerald-700">{{ number_format($row['converted']) }}</td>
                    <td class="whitespace-nowrap px-4 py-2 align-middle text-sm font-medium tabular-nums text-rose-700">{{ number_format($row['lost']) }}</td>
                    <td class="whitespace-nowrap px-4 py-2 align-middle text-sm font-semibold tabular-nums text-navy">{{ number_format($row['conversion_rate'], 1) }}%</td>
                    <td class="whitespace-nowrap px-4 py-2 align-middle">
                        <span @class(['inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset', $statusClass])>
                            {{ $row['status_label'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                        {{ $emptyMessage ?? __('No property type performance data for this period.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@props(['filter', 'search' => ''])

<div
    x-show="filtersOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-1"
    x-cloak
    class="border-t border-slate-100 px-3 py-4 sm:px-4"
>
    <form method="GET" action="{{ route('tenant.invoices.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @if ($search !== '')
            <input type="hidden" name="search" value="{{ $search }}">
        @endif
        <div>
            <label for="invoice_filter_payment" class="mb-1.5 block text-xs font-semibold text-slate-600">{{ __('Payment Status') }}</label>
            <select
                id="invoice_filter_payment"
                name="payment"
                class="block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
            >
                @foreach (\App\Enums\InvoicePaymentFilter::cases() as $option)
                    <option value="{{ $option->value }}" @selected($filter === $option)>{{ $option->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <x-ui.button type="submit" variant="default">{{ __('Apply') }}</x-ui.button>
            @if ($filter->isActive())
                <x-ui.button type="button" variant="soft" :href="route('tenant.invoices.index', array_filter(['search' => $search !== '' ? $search : null]))">{{ __('Clear') }}</x-ui.button>
            @endif
        </div>
    </form>
</div>

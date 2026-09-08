<x-app-layout :title="__('Adjustments') . ' | InSyte CRM'">
    <x-platform.billing-shell section="adjustments">
        <x-platform.page-header
            :title="__('Adjustments')"
            :description="__('See discounts and refunds that affected InSyte revenue.')"
        />

        <div class="grid gap-4 md:grid-cols-2">
            <a href="{{ route('platform.revenue.adjustments.discounts') }}" class="block rounded-xl border border-slate-100 bg-white p-5 shadow-sm transition-colors hover:border-slate-200 hover:bg-slate-50/60">
                <h2 class="text-lg font-semibold text-black">{{ __('Discounts') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Track discounts applied to Channel Partners.') }}</p>
            </a>
            <a href="{{ route('platform.revenue.adjustments.refunds') }}" class="block rounded-xl border border-slate-100 bg-white p-5 shadow-sm transition-colors hover:border-slate-200 hover:bg-slate-50/60">
                <h2 class="text-lg font-semibold text-black">{{ __('Refunds') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Track money returned to Channel Partners.') }}</p>
            </a>
        </div>
    </x-platform.billing-shell>
</x-app-layout>

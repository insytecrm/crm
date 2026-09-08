<x-app-layout :title="__('Start Onboarding') . ' | InSyte CRM'">
    <div class="mb-4">
        <a href="{{ route('platform.quotations.show', $quotation) }}" class="text-sm font-medium text-slate-500 hover:text-navy">← {{ __('Quotation') }} #{{ $quotation->number }}</a>
    </div>

    <x-platform.page-header
        :title="__('Start Onboarding')"
        :description="__('Create the Channel Partner workspace and admin login for handover.')"
    />

    <form method="POST" action="{{ route('platform.quotations.onboard.store', $quotation) }}" class="mx-auto max-w-2xl space-y-4">
        @csrf

        <x-platform.panel :title="__('From Quotation')" compact>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Company') }}</dt>
                    <dd class="font-medium text-black">{{ $partner['company_name'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Owner') }}</dt>
                    <dd class="font-medium text-black">{{ $partner['owner_name'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Plan') }}</dt>
                    <dd class="font-medium text-black">{{ $quotation->plan?->name ?? '—' }} · {{ $quotation->billing_cycle?->label() ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Amount') }}</dt>
                    <dd class="font-medium text-black">{{ $quotation->amountLabel() }}</dd>
                </div>
            </dl>
        </x-platform.panel>

        <x-platform.panel :title="__('Admin Login')" compact>
            <div class="space-y-4">
                <div>
                    <x-input-label for="admin_name" :value="__('Admin Name')" />
                    <x-text-input id="admin_name" name="admin_name" type="text" class="mt-1 block w-full" :value="old('admin_name', $quotation->owner_name)" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('admin_name')" />
                </div>
                <div>
                    <x-input-label for="admin_email" :value="__('Admin Email')" />
                    <x-text-input id="admin_email" name="admin_email" type="email" class="mt-1 block w-full" :value="old('admin_email', $quotation->email)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('admin_email')" />
                    <p class="mt-1 text-xs text-slate-500">{{ __('This is the login email you will hand over to the client.') }}</p>
                </div>
                <div>
                    <x-input-label for="slug" :value="__('Workspace slug (optional)')" />
                    <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug')" />
                    <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                </div>
            </div>
        </x-platform.panel>

        <div class="flex justify-end gap-2">
            <x-ui.button variant="outline" :href="route('platform.quotations.show', $quotation)">{{ __('Cancel') }}</x-ui.button>
            <x-ui.button type="submit" variant="default">{{ __('Create Client & Handover Login') }}</x-ui.button>
        </div>
    </form>
</x-app-layout>

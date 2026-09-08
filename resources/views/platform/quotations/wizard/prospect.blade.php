<x-app-layout :title="__('Create Quotation') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Create Quotation')"
        :description="__('Step 1 of 4 — Company')"
    >
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('platform.quotations')">{{ __('Cancel') }}</x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    <x-platform.quotation-wizard-steps :step="$step" />

    <form method="POST" action="{{ route('platform.quotations.wizard.prospect.store') }}" class="mx-auto max-w-2xl">
        @csrf
        <x-platform.panel :title="__('Company')">
            <div class="space-y-4">
                <div>
                    <x-input-label for="company_name" :value="__('Company Name')" />
                    <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $draft['company_name'] ?? '')" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                </div>
                <div>
                    <x-input-label for="owner_name" :value="__('Owner Name')" />
                    <x-text-input id="owner_name" name="owner_name" type="text" class="mt-1 block w-full" :value="old('owner_name', $draft['owner_name'] ?? '')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('owner_name')" />
                </div>
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $draft['email'] ?? '')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>
                <div>
                    <x-input-label for="phone" :value="__('Phone')" />
                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $draft['phone'] ?? '')" />
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <x-ui.button type="submit" variant="default">{{ __('Continue') }}</x-ui.button>
            </div>
        </x-platform.panel>
    </form>
</x-app-layout>

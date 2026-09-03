@php
    use App\Enums\SettingsTab;
@endphp

<div class="max-w-xl">
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-black">{{ SettingsTab::Company->label() }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ SettingsTab::Company->description() }}</p>
    </div>

    <form method="POST" action="{{ route('tenant.settings.company.update') }}" class="space-y-4">
        @csrf
        @method('PATCH')

        <div>
            <x-input-label for="company_name" :value="__('Company Name')" />
            <x-text-input id="company_name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $company->name)" required />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="company_email" :value="__('Company Email')" />
            <x-text-input id="company_email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $company->email)" autocomplete="email" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Workspace URL') }}</p>
            <p class="mt-1 text-sm font-medium text-black">{{ url('/'.$company->id) }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ __('Your team uses this link to sign in.') }}</p>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <x-ui.button type="submit" variant="default">{{ __('Save Company') }}</x-ui.button>
        </div>
    </form>
</div>

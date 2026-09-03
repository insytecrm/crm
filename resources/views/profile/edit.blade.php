<x-app-layout :title="__('Profile') . ' | InSyte CRM'">
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-black">{{ __('Profile') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('Manage your platform account settings') }}</p>
    </div>

    <div class="max-w-2xl space-y-6">
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            @include('profile.partials.update-password-form')
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>

<x-app-layout :title="__('Profile') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Profile')"
        :description="__('Manage your platform account settings')"
    />

    <div class="mx-auto max-w-2xl space-y-4">
        <x-platform.panel :title="__('Profile Information')">
            <x-slot:icon>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </x-slot:icon>

            @include('profile.partials.update-profile-information-form')
        </x-platform.panel>

        <x-platform.panel :title="__('Update Password')">
            <x-slot:icon>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </x-slot:icon>

            @include('profile.partials.update-password-form')
        </x-platform.panel>

        <x-platform.panel :title="__('Delete Account')">
            <x-slot:icon>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </x-slot:icon>

            @include('profile.partials.delete-user-form')
        </x-platform.panel>
    </div>
</x-app-layout>

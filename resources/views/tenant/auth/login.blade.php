<x-auth-layout :subtitle="__('Sign in to :company', ['company' => tenant('name')])">
    <x-auth.credentials-form :action="route('tenant.login')" />
</x-auth-layout>

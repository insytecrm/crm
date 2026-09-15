<x-auth-layout :subtitle="__('Sign in to :company', ['company' => tenant('name')])">
    <x-auth.credentials-form :action="route('tenant.login')" forgot-password forgot-password-route="tenant.password.request" />
</x-auth-layout>

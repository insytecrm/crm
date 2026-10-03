@props([
    'permission' => null,
    'feature' => null,
    'capability' => null,
])

@php
    $user = auth()->user();
    $allowed = $user !== null;

    if ($allowed && $permission !== null) {
        $allowed = $user->hasPermission($permission);
    }

    if ($allowed && $feature !== null) {
        $allowed = app(\App\Support\Platform\TenantPlanAccess::class)->hasFeature($feature);
    }

    if ($allowed && $capability !== null) {
        $allowed = app(\App\Support\Platform\TenantPlanAccess::class)->hasCapability($capability);
    }
@endphp

@if ($allowed)
    {{ $slot }}
@endif

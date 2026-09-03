@props([
    'permission',
])

@if (auth()->user()?->hasPermission($permission))
    {{ $slot }}
@endif

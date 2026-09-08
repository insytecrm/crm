@props([
    'status',
])

@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;

    [$classes, $label] = match ($value) {
        'active', 'paid', 'completed', 'accepted' => ['bg-emerald-50 text-emerald-700 ring-emerald-600/10', match ($value) {
            'paid' => __('Paid'),
            'completed' => __('Completed'),
            'accepted' => __('Accepted'),
            default => __('Active'),
        }],
        'trial', 'pending', 'processing', 'requested', 'sent' => ['bg-amber-50 text-amber-700 ring-amber-600/10', match ($value) {
            'pending' => __('Pending'),
            'processing' => __('Processing'),
            'requested' => __('Requested'),
            'sent' => __('Sent'),
            default => __('Trial'),
        }],
        'past_due', 'overdue' => ['bg-orange-50 text-orange-700 ring-orange-600/10', $value === 'overdue' ? __('Overdue') : __('Past Due')],
        'failed', 'suspended', 'cancelled', 'rejected' => ['bg-rose-50 text-rose-700 ring-rose-600/10', match ($value) {
            'failed' => __('Failed'),
            'cancelled' => __('Cancelled'),
            'rejected' => __('Rejected'),
            default => __('Suspended'),
        }],
        'paused', 'refunded', 'draft', 'expired' => ['bg-slate-100 text-slate-700 ring-slate-500/10', match ($value) {
            'refunded' => __('Refunded'),
            'draft' => __('Draft'),
            'expired' => __('Expired'),
            default => __('Paused'),
        }],
        default => ['bg-slate-100 text-slate-700 ring-slate-500/10', ucfirst(str_replace('_', ' ', $value))],
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize ring-1 ring-inset '.$classes]) }}>
    {{ $label }}
</span>

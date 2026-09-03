@props([
    'lead' => null,
    'class' => 'font-medium text-black hover:underline',
])

@if ($lead === null)
    <span {{ $attributes->merge(['class' => 'text-slate-400']) }}>—</span>
@else
    @php
        $leadPreview = [
            'id' => $lead->id,
            'name' => $lead->name,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'status' => $lead->status->label(),
            'source' => $lead->source,
            'budget' => $lead->budget?->label(),
            'location' => $lead->location,
            'assignedTo' => $lead->assignedTo?->name,
        ];
    @endphp

    <div
        {{ $attributes->merge(['class' => 'inline-flex']) }}
        data-lead-hover-card
        data-lead='@json($leadPreview)'
        data-class-name="{{ $class }}"
    ></div>
@endif

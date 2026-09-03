@include('tenant.leads.partials.details', [
    'lead' => $lead,
    'users' => $users,
    'inPlace' => true,
])

@include('tenant.leads.partials.mark-lost-modal', ['lead' => $lead])
@include('tenant.leads.partials.follow-up-modal', ['lead' => $lead])
@include('tenant.leads.partials.site-visit-modal', [
    'lead' => $lead,
    'properties' => $bookingProperties,
])
@if (! $lead->hasBooking())
    @include('tenant.bookings.partials.create-booking-modal', [
        'leads' => collect([$lead]),
        'properties' => $bookingProperties,
        'defaultLeadId' => $lead->id,
        'lockLead' => true,
    ])
@endif

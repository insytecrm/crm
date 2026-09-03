<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\LogLeadActivity;
use App\Enums\LeadActivityType;
use App\Enums\LeadClosingReason;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\MarkBookingAgreementRequest;
use App\Http\Requests\Tenant\StoreBookingInvoiceRequest;
use App\Http\Requests\Tenant\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\Property;
use App\Support\DataTable\DataTableViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::query()
            ->with(['property', 'lead', 'createdBy'])
            ->latest('booking_date')
            ->latest('id')
            ->get();

        return view('tenant.bookings.index', array_merge([
            'bookings' => $bookings,
            'leads' => Lead::query()
                ->whereDoesntHave('bookings')
                ->orderBy('name')
                ->get(['id', 'name']),
            'properties' => Property::bookingFormOptions(),
            'defaultLeadId' => $request->filled('lead') ? $request->integer('lead') : null,
            'openCreateModal' => $request->boolean('create'),
            'openModal' => old('_open_modal') ?? ($request->boolean('create') ? 'create-booking' : null),
        ], DataTableViewData::for($request->user(), 'bookings', $bookings)));
    }

    public function create(Request $request): RedirectResponse
    {
        return redirect()->route('tenant.bookings.index', array_filter([
            'create' => 1,
            'lead' => $request->filled('lead') ? $request->integer('lead') : null,
        ]));
    }

    public function store(StoreBookingRequest $request, LogLeadActivity $logLeadActivity): RedirectResponse
    {
        $configuration = $request->configuration();

        $booking = Booking::query()->create([
            'property_id' => $request->validated('property_id'),
            'lead_id' => $request->validated('lead_id'),
            'configuration_index' => $request->validated('configuration_index'),
            'configuration_name' => $configuration['name'],
            'unit_number' => $request->validated('unit_number'),
            'agreement_value' => $request->validated('agreement_value'),
            'booking_date' => $request->validated('booking_date'),
            'created_by_id' => auth()->id(),
        ]);

        $booking->loadMissing('property', 'lead');

        $lead = $booking->lead;

        if ($lead->status !== LeadStatus::Converted) {
            $previousStatus = $lead->status;

            $lead->update([
                'status' => LeadStatus::Converted,
                'closing_reason' => LeadClosingReason::Converted,
                'closed_at' => now(),
            ]);

            $logLeadActivity->handle(
                $lead,
                LeadActivityType::StatusChanged,
                __('Status changed from :from to :to', [
                    'from' => $previousStatus->label(),
                    'to' => LeadStatus::Converted->label(),
                ]),
                metadata: [
                    'from' => $previousStatus->value,
                    'to' => LeadStatus::Converted->value,
                ],
            );
        }

        $logLeadActivity->handle(
            $lead,
            LeadActivityType::BookingCreated,
            __('Booking created: :property — :configuration, Unit :unit', [
                'property' => $booking->property->project_name,
                'configuration' => $booking->configuration_name,
                'unit' => $booking->unit_number,
            ]),
            metadata: ['booking_id' => $booking->id],
        );

        return redirect()
            ->route('tenant.bookings.index')
            ->with('status', __('Booking created.'));
    }

    public function markAgreement(Booking $booking, MarkBookingAgreementRequest $request): RedirectResponse
    {
        abort_unless($booking->canMarkAgreement(), 404);

        $booking->update([
            'agreement_date' => $request->validated('agreement_date'),
            'agreement_value' => $request->validated('agreement_value'),
            'payout_percent' => $request->validated('payout_percent'),
            'payout_amount' => $request->validated('payout_amount'),
        ]);

        return redirect()
            ->route('tenant.bookings.index')
            ->with('status', __('Agreement marked for booking.'));
    }

    public function storeInvoice(Booking $booking, StoreBookingInvoiceRequest $request): RedirectResponse
    {
        abort_unless($booking->canCreateInvoice(), 404);

        $booking->update([
            'invoice_date' => $request->validated('invoice_date'),
            'invoice_number' => Booking::invoiceNumberFor($booking->id),
            'invoiced_at' => now(),
        ]);

        return redirect()
            ->route('tenant.bookings.index')
            ->with('status', __('Invoice created for booking.'));
    }
}

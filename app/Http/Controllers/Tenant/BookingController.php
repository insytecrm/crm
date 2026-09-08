<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\LogLeadActivity;
use App\Actions\LogLeadBookingMilestone;
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
use App\Queries\BookingStatistics;
use App\Support\DataTable\DataTableViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request, BookingStatistics $bookingStatistics): View
    {
        $search = $request->string('search')->trim()->toString();

        $bookings = Booking::query()
            ->with(['property', 'lead', 'createdBy'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('unit_number', 'like', "%{$search}%")
                        ->orWhere('configuration_name', 'like', "%{$search}%")
                        ->orWhereHas('lead', fn ($leadQuery) => $leadQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('property', fn ($propertyQuery) => $propertyQuery->where('project_name', 'like', "%{$search}%"));
                });
            })
            ->latest('booking_date')
            ->latest('id')
            ->get();

        return view('tenant.bookings.index', array_merge([
            'bookings' => $bookings,
            'search' => $search,
            'statistics' => $bookingStatistics->forTenant(),
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

    public function markAgreement(MarkBookingAgreementRequest $request, Booking $booking, LogLeadBookingMilestone $logLeadBookingMilestone): RedirectResponse
    {
        if (! $booking->canMarkAgreement()) {
            return redirect()
                ->route('tenant.bookings.index')
                ->with('status', __('This booking already has an agreement.'));
        }

        $booking->update([
            'agreement_date' => $request->validated('agreement_date'),
            'agreement_value' => $request->validated('agreement_value'),
            'payout_percent' => $request->validated('payout_percent'),
            'payout_amount' => $request->validated('payout_amount'),
        ]);

        $logLeadBookingMilestone->agreement($booking);

        return redirect()
            ->route('tenant.bookings.index')
            ->with('status', __('Agreement marked for booking.'));
    }

    public function storeInvoice(StoreBookingInvoiceRequest $request, Booking $booking, LogLeadBookingMilestone $logLeadBookingMilestone): RedirectResponse
    {
        if (! $booking->canCreateInvoice()) {
            return redirect()
                ->route('tenant.bookings.index')
                ->with('status', __('This booking cannot be invoiced yet.'));
        }

        $booking->update([
            'invoice_date' => $request->validated('invoice_date'),
            'invoice_number' => Booking::invoiceNumberFor($booking->id),
            'invoiced_at' => now(),
        ]);

        $logLeadBookingMilestone->invoice($booking);

        return redirect()
            ->route('tenant.bookings.index')
            ->with('status', __('Invoice created for booking.'));
    }
}

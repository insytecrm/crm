<?php

namespace App\Actions;

use App\Enums\LeadActivityType;
use App\Models\Booking;

class LogLeadBookingMilestone
{
    public function __construct(private LogLeadActivity $logLeadActivity) {}

    public function agreement(Booking $booking): void
    {
        $booking->loadMissing('lead', 'property');

        if ($booking->lead === null) {
            return;
        }

        $this->logLeadActivity->handle(
            $booking->lead,
            LeadActivityType::AgreementMarked,
            __('Agreement marked: :property — Unit :unit', [
                'property' => $booking->property?->project_name ?? __('Unknown Project'),
                'unit' => $booking->unit_number,
            ]),
            metadata: ['booking_id' => $booking->id],
        );
    }

    public function invoice(Booking $booking): void
    {
        $booking->loadMissing('lead', 'property');

        if ($booking->lead === null) {
            return;
        }

        $this->logLeadActivity->handle(
            $booking->lead,
            LeadActivityType::InvoiceCreated,
            __('Invoice created: :number for :property — Unit :unit', [
                'number' => $booking->invoice_number ?? Booking::invoiceNumberFor($booking->id),
                'property' => $booking->property?->project_name ?? __('Unknown Project'),
                'unit' => $booking->unit_number,
            ]),
            metadata: ['booking_id' => $booking->id],
        );
    }

    public function payoutReceived(Booking $booking): void
    {
        $booking->loadMissing('lead', 'property');

        if ($booking->lead === null) {
            return;
        }

        $this->logLeadActivity->handle(
            $booking->lead,
            LeadActivityType::PayoutReceived,
            __('Payout received: ₹:amount for :property — Unit :unit', [
                'amount' => number_format($booking->payout_amount ?? 0),
                'property' => $booking->property?->project_name ?? __('Unknown Project'),
                'unit' => $booking->unit_number,
            ]),
            metadata: ['booking_id' => $booking->id],
        );
    }
}

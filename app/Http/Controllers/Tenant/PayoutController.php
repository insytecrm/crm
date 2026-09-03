<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\LogLeadBookingMilestone;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;

class PayoutController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('tenant.invoices.index');
    }

    public function markPaid(Booking $booking, LogLeadBookingMilestone $logLeadBookingMilestone): RedirectResponse
    {
        abort_unless($booking->canMarkPayoutPaid(), 404);

        $booking->update([
            'payout_paid_at' => now(),
        ]);

        $logLeadBookingMilestone->payoutReceived($booking);

        return redirect()
            ->route('tenant.invoices.index')
            ->with('status', __('Invoice marked as paid.'));
    }
}

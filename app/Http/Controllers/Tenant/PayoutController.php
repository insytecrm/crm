<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Support\DataTable\DataTableViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayoutController extends Controller
{
    public function index(Request $request): View
    {
        $payouts = Booking::query()
            ->whereNotNull('agreement_date')
            ->with(['property', 'lead'])
            ->latest('agreement_date')
            ->latest('id')
            ->get();

        return view('tenant.payouts.index', array_merge([
            'payouts' => $payouts,
        ], DataTableViewData::for($request->user(), 'payouts', $payouts)));
    }

    public function markPaid(Booking $booking): RedirectResponse
    {
        abort_unless($booking->canMarkPayoutPaid(), 404);

        $booking->update([
            'payout_paid_at' => now(),
        ]);

        return redirect()
            ->route('tenant.payouts.index')
            ->with('status', __('Payout marked as paid.'));
    }
}

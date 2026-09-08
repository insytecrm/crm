<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\BillingDiscount;
use App\Models\BillingRefund;
use Illuminate\View\View;

class BillingAdjustmentController extends Controller
{
    public function index(): View
    {
        return view('platform.revenue.adjustments.index');
    }

    public function discounts(): View
    {
        $discounts = BillingDiscount::query()
            ->with(['tenant', 'invoice', 'appliedByUser'])
            ->latest('applied_at')
            ->paginate(20);

        return view('platform.revenue.adjustments.discounts', [
            'discounts' => $discounts,
        ]);
    }

    public function refunds(): View
    {
        $refunds = BillingRefund::query()
            ->with(['tenant', 'payment'])
            ->latest('refunded_at')
            ->latest('id')
            ->paginate(20);

        return view('platform.revenue.adjustments.refunds', [
            'refunds' => $refunds,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Platform;

use App\Actions\ApplyPartnerBillingDiscount;
use App\Actions\CancelPartnerSubscription;
use App\Actions\ChangePartnerSubscriptionPlan;
use App\Actions\ExtendPartnerSubscriptionTrial;
use App\Actions\PausePartnerSubscription;
use App\Actions\ResumePartnerSubscription;
use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\ApplyPartnerBillingDiscountRequest;
use App\Http\Requests\Platform\ChangePartnerSubscriptionPlanRequest;
use App\Http\Requests\Platform\ExtendPartnerSubscriptionTrialRequest;
use App\Models\BillingInvoice;
use App\Models\PartnerSubscription;
use App\Models\Plan;
use App\Support\Platform\BillingMoney;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingSubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = $request->string('status')->toString() ?: 'all';

        $query = PartnerSubscription::query()
            ->with(['tenant', 'plan'])
            ->latest('id');

        if ($search !== '') {
            $query->whereHas('tenant', function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('id', 'like', '%'.$search.'%');
            });
        }

        if ($status === 'expiring_soon') {
            $query->expiringSoon();
        } elseif ($status !== 'all' && SubscriptionStatus::tryFrom($status) !== null) {
            $query->where('status', $status);
        }

        $subscriptions = $query->paginate(20)->withQueryString();

        return view('platform.revenue.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'statusOptions' => [
                ['value' => 'all', 'label' => __('All')],
                ['value' => SubscriptionStatus::Active->value, 'label' => __('Active')],
                ['value' => SubscriptionStatus::Trial->value, 'label' => __('Trial')],
                ['value' => SubscriptionStatus::PastDue->value, 'label' => __('Past Due')],
                ['value' => SubscriptionStatus::Cancelled->value, 'label' => __('Cancelled')],
                ['value' => 'expiring_soon', 'label' => __('Expiring Soon')],
            ],
        ]);
    }

    public function show(PartnerSubscription $subscription): View
    {
        $subscription->load(['tenant', 'plan']);

        $invoices = BillingInvoice::query()
            ->where('partner_subscription_id', $subscription->id)
            ->latest('issued_at')
            ->limit(50)
            ->get();

        $latestPayment = $subscription->payments()->latest('payment_date')->latest('id')->first();

        return view('platform.revenue.subscriptions.show', [
            'subscription' => $subscription,
            'invoices' => $invoices,
            'plans' => Plan::query()->active()->orderBy('price_monthly')->get(),
            'paymentStatus' => $latestPayment?->status?->label() ?? __('—'),
            'priceLabel' => BillingMoney::format((int) $subscription->amount).($subscription->billing_cycle?->priceSuffix() ?? ''),
        ]);
    }

    public function changePlan(
        ChangePartnerSubscriptionPlanRequest $request,
        PartnerSubscription $subscription,
        ChangePartnerSubscriptionPlan $action,
    ): RedirectResponse {
        $plan = Plan::query()->findOrFail($request->integer('plan_id'));
        $cycle = BillingCycle::from($request->string('billing_cycle')->toString());

        $action->handle($subscription, $plan, $cycle);

        return back()->with('status', __('Subscription plan updated.'));
    }

    public function extendTrial(
        ExtendPartnerSubscriptionTrialRequest $request,
        PartnerSubscription $subscription,
        ExtendPartnerSubscriptionTrial $action,
    ): RedirectResponse {
        $action->handle($subscription, $request->integer('days'));

        return back()->with('status', __('Trial extended.'));
    }

    public function pause(PartnerSubscription $subscription, PausePartnerSubscription $action): RedirectResponse
    {
        $action->handle($subscription);

        return back()->with('status', __('Subscription paused.'));
    }

    public function resume(PartnerSubscription $subscription, ResumePartnerSubscription $action): RedirectResponse
    {
        $action->handle($subscription);

        return back()->with('status', __('Subscription resumed.'));
    }

    public function cancel(PartnerSubscription $subscription, CancelPartnerSubscription $action): RedirectResponse
    {
        $action->handle($subscription);

        return back()->with('status', __('Subscription cancelled.'));
    }

    public function applyDiscount(
        ApplyPartnerBillingDiscountRequest $request,
        PartnerSubscription $subscription,
        ApplyPartnerBillingDiscount $action,
    ): RedirectResponse {
        $action->handle($subscription, [
            'amount' => $request->integer('amount'),
            'reason' => $request->string('reason')->toString(),
        ]);

        return back()->with('status', __('Discount applied.'));
    }
}

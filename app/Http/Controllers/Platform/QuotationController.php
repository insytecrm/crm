<?php

namespace App\Http\Controllers\Platform;

use App\Actions\AcceptQuotation;
use App\Actions\CreateSubscriptionFromQuotation;
use App\Actions\DuplicateQuotation;
use App\Actions\ExpireQuotations;
use App\Actions\OnboardQuotationPartner;
use App\Actions\RejectQuotation;
use App\Actions\SendQuotation;
use App\Actions\UpdateQuotation;
use App\Enums\BillingCycle;
use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\AcceptQuotationRequest;
use App\Http\Requests\Platform\OnboardQuotationRequest;
use App\Http\Requests\Platform\UpdateQuotationRequest;
use App\Models\Plan;
use App\Models\PlatformLead;
use App\Models\Quotation;
use App\Support\Platform\BillingMoney;
use App\Support\Platform\QuotationPricing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function index(Request $request, ExpireQuotations $expireQuotations): View
    {
        $expireQuotations->handle();

        $search = trim((string) $request->string('search'));
        $status = $request->string('status')->toString() ?: 'all';
        $date = $request->string('date')->toString();

        $query = Quotation::query()
            ->with(['tenant', 'plan'])
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('number', 'like', '%'.$search.'%')
                    ->orWhere('company_name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhereHas('tenant', function ($tenantQuery) use ($search): void {
                        $tenantQuery
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('id', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($status !== 'all' && QuotationStatus::tryFrom($status) !== null) {
            $query->where('status', $status);
        }

        if ($date !== '') {
            $query->whereDate('created_at', $date);
        }

        $summary = [
            'total' => Quotation::query()->count(),
            'draft' => Quotation::query()->where('status', QuotationStatus::Draft)->count(),
            'sent' => Quotation::query()->where('status', QuotationStatus::Sent)->count(),
            'accepted' => Quotation::query()->where('status', QuotationStatus::Accepted)->count(),
            'expired' => Quotation::query()->where('status', QuotationStatus::Expired)->count(),
        ];

        return view('platform.quotations.index', [
            'quotations' => $query->paginate(20)->withQueryString(),
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'date' => $date,
            ],
            'statusOptions' => [
                ['value' => 'all', 'label' => __('All')],
                ['value' => QuotationStatus::Draft->value, 'label' => QuotationStatus::Draft->label()],
                ['value' => QuotationStatus::Sent->value, 'label' => QuotationStatus::Sent->label()],
                ['value' => QuotationStatus::Accepted->value, 'label' => QuotationStatus::Accepted->label()],
                ['value' => QuotationStatus::Rejected->value, 'label' => QuotationStatus::Rejected->label()],
                ['value' => QuotationStatus::Expired->value, 'label' => QuotationStatus::Expired->label()],
            ],
            'quotationPlans' => $this->quotationPlans(),
            'leadSearchOptions' => PlatformLead::quotationSelectOptions(),
            'openQuotationModal' => $request->boolean('quote') || old('_quotation_wizard') === '1',
            'defaultTaxRate' => QuotationPricing::DefaultTaxRate,
        ]);
    }

    public function show(Quotation $quotation, ExpireQuotations $expireQuotations): View
    {
        $expireQuotations->handle();
        $quotation->refresh()->load(['tenant', 'plan', 'subscription', 'platformLead']);

        return view('platform.quotations.show', [
            'quotation' => $quotation,
            'partner' => $this->prospectDetails($quotation),
            'handover' => session('quotation_handover'),
        ]);
    }

    public function edit(Quotation $quotation): RedirectResponse|View
    {
        if (! $quotation->canEdit()) {
            return redirect()
                ->route('platform.quotations.show', $quotation)
                ->withErrors(['quotation' => __('Only draft quotations can be edited. Duplicate to create a new quotation.')]);
        }

        $quotation->load(['tenant', 'plan']);

        return view('platform.quotations.edit', [
            'quotation' => $quotation,
            'partner' => $this->prospectDetails($quotation),
            'plans' => Plan::query()->active()->orderBy('price_monthly')->get(),
            'billingCycles' => BillingCycle::cases(),
        ]);
    }

    public function update(
        UpdateQuotationRequest $request,
        Quotation $quotation,
        UpdateQuotation $action,
    ): RedirectResponse {
        $action->handle($quotation, $request->validated());

        return redirect()
            ->route('platform.quotations.show', $quotation)
            ->with('status', __('Quotation updated.'));
    }

    public function send(Quotation $quotation, SendQuotation $action): RedirectResponse
    {
        $action->handle($quotation);

        return redirect()
            ->back()
            ->with('status', __('Quotation sent.'));
    }

    public function accept(
        AcceptQuotationRequest $request,
        Quotation $quotation,
        AcceptQuotation $action,
    ): RedirectResponse {
        $action->handle($quotation, $request->validated('accepted_by_name'));

        return redirect()
            ->route('platform.quotations.show', $quotation)
            ->with('status', __('Quotation marked as accepted.'));
    }

    public function reject(Quotation $quotation, RejectQuotation $action): RedirectResponse
    {
        $action->handle($quotation);

        return redirect()
            ->route('platform.quotations.show', $quotation)
            ->with('status', __('Quotation marked as rejected.'));
    }

    public function duplicate(Quotation $quotation, DuplicateQuotation $action): RedirectResponse
    {
        $copy = $action->handle($quotation);

        return redirect()
            ->route('platform.quotations.show', $copy)
            ->with('status', __('Quotation duplicated.'));
    }

    public function download(Quotation $quotation): Response
    {
        $quotation->load(['tenant', 'plan']);

        $lines = [
            __('Quotation').': #'.$quotation->number,
            __('Company').': '.$quotation->companyDisplayName(),
            __('Owner').': '.($quotation->owner_name ?: '—'),
            __('Email').': '.($quotation->email ?: '—'),
            __('Plan').': '.($quotation->plan?->name ?? '—'),
            __('Billing').': '.($quotation->billing_cycle?->label() ?? '—'),
            __('Plan Price').': '.BillingMoney::format((int) $quotation->plan_price),
            __('Discount').': '.BillingMoney::format((int) $quotation->discount_amount),
            __('Tax').': '.BillingMoney::format((int) $quotation->tax_amount),
            __('Total').': '.BillingMoney::format((int) $quotation->total),
            __('Trial').': '.$quotation->trialLabel(),
            __('Valid Until').': '.($quotation->valid_until?->timezone(config('app.timezone'))->format('d M Y') ?? '—'),
            __('Status').': '.($quotation->status?->label() ?? '—'),
            __('Created').': '.($quotation->created_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—'),
        ];

        return response(implode(PHP_EOL, $lines).PHP_EOL, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$quotation->number.'.txt"',
        ]);
    }

    public function onboardForm(Quotation $quotation): RedirectResponse|View
    {
        if (! $quotation->canStartOnboarding()) {
            return redirect()
                ->route('platform.quotations.show', $quotation)
                ->withErrors(['quotation' => __('Onboarding is only available for accepted quotations that are not yet onboarded.')]);
        }

        $quotation->load('plan');

        return view('platform.quotations.onboard', [
            'quotation' => $quotation,
            'partner' => $this->prospectDetails($quotation),
        ]);
    }

    public function onboard(
        OnboardQuotationRequest $request,
        Quotation $quotation,
        OnboardQuotationPartner $action,
    ): RedirectResponse {
        $result = $action->handle($quotation, $request->validated());

        return redirect()
            ->route('platform.quotations.show', $result['quotation'])
            ->with('status', __('Client onboarded. Hand over the login details below.'))
            ->with('quotation_handover', [
                'company_name' => $result['tenant']->name,
                'admin_name' => $result['admin_name'],
                'admin_email' => $result['admin_email'],
                'admin_password' => $result['admin_password'],
                'login_url' => $result['login_url'],
                'subscription_url' => route('platform.revenue.subscriptions.show', $result['subscription']),
                'partner_url' => route('tenants.show', $result['tenant']),
            ]);
    }

    public function createSubscription(
        Quotation $quotation,
        CreateSubscriptionFromQuotation $action,
    ): RedirectResponse {
        $subscription = $action->handle($quotation);

        return redirect()
            ->route('platform.revenue.subscriptions.show', $subscription)
            ->with('status', __('Subscription and first invoice created from quotation.'));
    }

    /**
     * @return array{company_name: string, owner_name: string, email: string, phone: string}
     */
    private function prospectDetails(Quotation $quotation): array
    {
        return [
            'company_name' => $quotation->companyDisplayName(),
            'owner_name' => (is_string($quotation->owner_name) && $quotation->owner_name !== '') ? $quotation->owner_name : '—',
            'email' => $quotation->email ?: '—',
            'phone' => (is_string($quotation->phone) && $quotation->phone !== '') ? $quotation->phone : '—',
        ];
    }

    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     price_monthly: int,
     *     price_annual: int,
     *     trial_enabled: bool,
     *     trial_days: int|null
     * }>
     */
    private function quotationPlans(): array
    {
        return Plan::query()
            ->active()
            ->orderBy('price_monthly')
            ->get()
            ->map(fn (Plan $plan): array => [
                'id' => $plan->id,
                'name' => $plan->name,
                'price_monthly' => (int) $plan->price_monthly,
                'price_annual' => (int) $plan->price_annual,
                'trial_enabled' => (bool) $plan->trial_enabled,
                'trial_days' => $plan->trial_enabled ? (int) $plan->trial_days : null,
            ])
            ->all();
    }
}

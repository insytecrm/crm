<?php

namespace App\Http\Controllers\Platform;

use App\Actions\CreateQuotation;
use App\Enums\BillingCycle;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreQuotationModalRequest;
use App\Http\Requests\Platform\StoreQuotationWizardPlanRequest;
use App\Http\Requests\Platform\StoreQuotationWizardPricingRequest;
use App\Http\Requests\Platform\StoreQuotationWizardProspectRequest;
use App\Http\Requests\Platform\StoreQuotationWizardReviewRequest;
use App\Models\Plan;
use App\Support\Platform\QuotationPricing;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuotationWizardController extends Controller
{
    public const SessionKey = 'quotation_wizard';

    public function create(): RedirectResponse
    {
        session()->forget(self::SessionKey);

        return redirect()->route('platform.quotations', ['quote' => 1]);
    }

    public function prospect(): View
    {
        return view('platform.quotations.wizard.prospect', $this->wizardView(1));
    }

    public function storeProspect(StoreQuotationWizardProspectRequest $request): RedirectResponse
    {
        $this->mergeDraft($request->validated());

        return redirect()->route('platform.quotations.wizard.plan');
    }

    public function plan(): RedirectResponse|View
    {
        if (! $this->hasDraftKeys(['company_name', 'owner_name', 'email'])) {
            return redirect()->route('platform.quotations.wizard.prospect');
        }

        return view('platform.quotations.wizard.plan', $this->wizardView(2, [
            'plans' => Plan::query()->active()->orderBy('price_monthly')->get(),
            'billingCycles' => BillingCycle::cases(),
        ]));
    }

    public function storePlan(StoreQuotationWizardPlanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $plan = Plan::query()->findOrFail($data['plan_id']);
        $cycle = BillingCycle::from($data['billing_cycle']);
        $planPrice = $cycle === BillingCycle::Annual
            ? (int) $plan->price_annual
            : (int) $plan->price_monthly;

        $this->mergeDraft([
            ...$data,
            'plan_price' => $planPrice,
            'discount_amount' => (int) ($this->draft()['discount_amount'] ?? 0),
            'tax_amount' => QuotationPricing::defaultTax($planPrice, (int) ($this->draft()['discount_amount'] ?? 0)),
        ]);

        return redirect()->route('platform.quotations.wizard.pricing');
    }

    public function pricing(): RedirectResponse|View
    {
        if (! $this->hasDraftKeys(['company_name', 'plan_id', 'billing_cycle', 'plan_price'])) {
            return redirect()->route('platform.quotations.wizard.plan');
        }

        $draft = $this->draft();
        $pricing = QuotationPricing::calculate(
            (int) $draft['plan_price'],
            (int) ($draft['discount_amount'] ?? 0),
            array_key_exists('tax_amount', $draft) ? (int) $draft['tax_amount'] : null,
        );

        return view('platform.quotations.wizard.pricing', $this->wizardView(3, [
            'pricing' => $pricing,
            'defaultTaxRate' => QuotationPricing::DefaultTaxRate,
        ]));
    }

    public function storePricing(StoreQuotationWizardPricingRequest $request): RedirectResponse
    {
        $this->mergeDraft($request->validated());

        return redirect()->route('platform.quotations.wizard.review');
    }

    public function review(): RedirectResponse|View
    {
        if (! $this->hasDraftKeys(['company_name', 'owner_name', 'email', 'plan_id', 'billing_cycle', 'plan_price', 'discount_amount', 'tax_amount'])) {
            return redirect()->route('platform.quotations.wizard.pricing');
        }

        $draft = $this->draft();
        $plan = Plan::query()->findOrFail($draft['plan_id']);
        $pricing = QuotationPricing::calculate(
            (int) $draft['plan_price'],
            (int) $draft['discount_amount'],
            (int) $draft['tax_amount'],
        );

        return view('platform.quotations.wizard.review', $this->wizardView(4, [
            'plan' => $plan,
            'pricing' => $pricing,
            'billingCycle' => BillingCycle::from($draft['billing_cycle']),
            'validUntil' => $draft['valid_until'] ?? now()->addDays(7)->toDateString(),
        ]));
    }

    public function store(StoreQuotationWizardReviewRequest $request, CreateQuotation $createQuotation): RedirectResponse
    {
        if (! $this->hasDraftKeys(['company_name', 'owner_name', 'email', 'plan_id', 'billing_cycle', 'plan_price', 'discount_amount', 'tax_amount'])) {
            return redirect()->route('platform.quotations.wizard.prospect');
        }

        $draft = array_merge($this->draft(), $request->validated());
        $quotation = $createQuotation->handle($draft);

        session()->forget(self::SessionKey);

        return redirect()
            ->route('platform.quotations.show', $quotation)
            ->with('status', __('Quotation created.'));
    }

    public function storeModal(StoreQuotationModalRequest $request, CreateQuotation $createQuotation): RedirectResponse
    {
        $quotation = $createQuotation->handle($request->validated());

        return redirect()
            ->back()
            ->with('status', __('Quotation created.'));
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function wizardView(int $step, array $extra = []): array
    {
        return array_merge([
            'step' => $step,
            'draft' => $this->draft(),
        ], $extra);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function mergeDraft(array $data): void
    {
        session()->put(self::SessionKey, array_merge($this->draft(), $data));
    }

    /**
     * @return array<string, mixed>
     */
    private function draft(): array
    {
        $draft = session(self::SessionKey, []);

        return is_array($draft) ? $draft : [];
    }

    /**
     * @param  list<string>  $keys
     */
    private function hasDraftKeys(array $keys): bool
    {
        $draft = $this->draft();

        foreach ($keys as $key) {
            if (! array_key_exists($key, $draft) || $draft[$key] === null || $draft[$key] === '') {
                return false;
            }
        }

        return true;
    }
}

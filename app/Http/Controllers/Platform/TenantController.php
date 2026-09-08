<?php

namespace App\Http\Controllers\Platform;

use App\Actions\CreateTenant;
use App\Contracts\PlatformPlanCatalog;
use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Models\Plan;
use App\Models\Tenant;
use App\Support\Platform\ChannelPartnerListing;
use App\Support\Platform\QuotationPricing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    /**
     * Display a listing of channel partners.
     */
    public function index(Request $request, ChannelPartnerListing $listing, PlatformPlanCatalog $plans): View
    {
        return view('platform.tenants.index', array_merge($listing->forRequest($request), [
            'plans' => $plans->options(),
            'openAddModal' => $request->boolean('add') || old('_wizard') === '1',
            'quotationPlans' => Plan::query()
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
                ->all(),
            'openQuotationModal' => $request->boolean('quote') || old('_quotation_wizard') === '1',
            'defaultTaxRate' => QuotationPricing::DefaultTaxRate,
        ]));
    }

    /**
     * Guided onboarding entry point.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('tenants.index', ['quote' => 1]);
    }

    /**
     * Store a newly created company and provision its database.
     */
    public function store(StoreTenantRequest $request, CreateTenant $createTenant): RedirectResponse
    {
        $tenant = $createTenant->handle($request->safe()->only([
            'slug',
            'name',
            'email',
            'status',
            'admin_name',
            'admin_email',
            'admin_password',
        ]));

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('status', __('Channel Partner created.'));
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Tenant $tenant): View
    {
        return view('platform.tenants.edit', [
            'tenant' => $tenant,
            'statuses' => TenantStatus::cases(),
        ]);
    }

    /**
     * Update the specified company.
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $tenant->update($request->safe()->only([
            'name',
            'email',
            'status',
            'owner_name',
            'phone',
            'location',
        ]));

        if ($request->input('_return_to') === 'index') {
            return redirect()
                ->route('tenants.index')
                ->with('status', __('Channel Partner updated.'));
        }

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('status', __('Channel Partner updated.'));
    }

    /**
     * Remove the specified company and its database.
     */
    public function destroy(Tenant $tenant): RedirectResponse
    {
        if ($tenant->hasActiveSubscription()) {
            return redirect()
                ->route('tenants.index')
                ->withErrors([
                    'tenant' => __('Channel partners with an active subscription cannot be deleted.'),
                ]);
        }

        $tenant->delete();

        return redirect()
            ->route('tenants.index')
            ->with('status', __('Channel Partner deleted.'));
    }
}

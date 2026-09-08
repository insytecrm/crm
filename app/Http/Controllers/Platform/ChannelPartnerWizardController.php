<?php

namespace App\Http\Controllers\Platform;

use App\Actions\CreateTenant;
use App\Contracts\PlatformPlanCatalog;
use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChannelPartnerWizardRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChannelPartnerWizardController extends Controller
{
    /**
     * Entry point — open the Channel Partners list with the add modal.
     */
    public function company(): RedirectResponse
    {
        return redirect()->route('tenants.index', ['quote' => 1]);
    }

    public function store(
        StoreChannelPartnerWizardRequest $request,
        CreateTenant $createTenant,
        PlatformPlanCatalog $plans,
    ): RedirectResponse {
        $data = $request->validated();
        $plan = $plans->find($data['plan_key']);

        if ($plan === null) {
            return redirect()
                ->route('tenants.index', ['add' => 1])
                ->withInput()
                ->withErrors(['plan_key' => __('Select a valid plan.')]);
        }

        $slug = $data['slug'] ?? null;
        if ($slug === null || $slug === '') {
            $slug = $this->uniqueSlugFromName($data['name']);
        }

        $startTrial = $request->boolean('start_trial');

        $tenant = $createTenant->handle([
            'slug' => $slug,
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => TenantStatus::Active->value,
            'admin_name' => $data['admin_name'],
            'admin_email' => $data['admin_email'],
            'admin_password' => Str::password(16),
            'owner_name' => $data['owner_name'],
            'phone' => $data['phone'] ?? null,
            'location' => $data['location'] ?? null,
            'plan_key' => $plan['key'],
            'billing_cycle' => $data['billing_cycle'],
            'trial_days' => $startTrial ? (int) ($data['trial_days'] ?? 7) : null,
        ]);

        return redirect()->route('tenants.wizard.success', $tenant);
    }

    public function success(Tenant $tenant): View
    {
        return view('platform.tenants.wizard.success', [
            'tenant' => $tenant,
        ]);
    }

    private function uniqueSlugFromName(string $name): string
    {
        $base = Str::lower(preg_replace('/[^a-z0-9]+/i', '', Str::ascii($name)) ?: 'partner');
        $base = preg_replace('/^[0-9]+/', '', $base) ?: 'partner';
        $base = Str::limit($base, 28, '');

        $slug = $base;
        $suffix = 1;

        while (
            in_array($slug, Tenant::ReservedIds, true)
            || Tenant::query()->whereKey($slug)->exists()
        ) {
            $slug = Str::limit($base, 28, '').$suffix;
            $suffix++;
        }

        return $slug;
    }
}

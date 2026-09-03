<?php

namespace App\Http\Controllers\Platform;

use App\Actions\CreateTenant;
use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantController extends Controller
{
    /**
     * Display a listing of companies.
     */
    public function index(): View
    {
        $tenants = Tenant::query()
            ->latest()
            ->orderByDesc('id')
            ->paginate(15);

        return view('platform.tenants.index', [
            'tenants' => $tenants,
        ]);
    }

    /**
     * Show the form for creating a new company.
     */
    public function create(): View
    {
        return view('platform.tenants.create', [
            'statuses' => TenantStatus::cases(),
        ]);
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
            ->with('status', 'Company created.');
    }

    /**
     * Display the specified company.
     */
    public function show(Tenant $tenant): View
    {
        return view('platform.tenants.show', [
            'tenant' => $tenant,
        ]);
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
        ]));

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('status', 'Company updated.');
    }

    /**
     * Remove the specified company and its database.
     */
    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->delete();

        return redirect()
            ->route('tenants.index')
            ->with('status', 'Company deleted.');
    }
}

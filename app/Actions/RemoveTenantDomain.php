<?php

namespace App\Actions;

use App\Enums\DomainPurpose;
use App\Models\Tenant;

class RemoveTenantDomain
{
    public function handle(Tenant $tenant, DomainPurpose $purpose): void
    {
        $tenant->domainFor($purpose)?->delete();
    }
}

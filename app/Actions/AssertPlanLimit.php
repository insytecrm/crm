<?php

namespace App\Actions;

use App\Enums\PlanLimitKey;
use App\Support\Platform\TenantPlanAccess;

class AssertPlanLimit
{
    public function __construct(private TenantPlanAccess $access) {}

    public function handle(PlanLimitKey $key, int $amount = 1): void
    {
        $this->access->assertCanConsume($key, $amount);
    }
}

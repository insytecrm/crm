<?php

namespace App\Actions;

use App\Enums\PlanLimitKey;
use App\Models\Automation;
use App\Models\User;

class CreateAutomationWorkflow
{
    public function __construct(private AssertPlanLimit $assertPlanLimit) {}

    public function handle(User $user): Automation
    {
        $this->assertPlanLimit->handle(PlanLimitKey::Automations);

        return Automation::query()->create([
            'user_id' => $user->id,
            'name' => __('Untitled workflow'),
            'is_active' => false,
            'trigger' => null,
        ]);
    }
}

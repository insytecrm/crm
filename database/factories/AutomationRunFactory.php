<?php

namespace Database\Factories;

use App\Enums\AutomationRunStatus;
use App\Enums\AutomationTrigger;
use App\Models\Automation;
use App\Models\AutomationRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AutomationRun>
 */
class AutomationRunFactory extends Factory
{
    protected $model = AutomationRun::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'automation_id' => Automation::factory(),
            'user_id' => User::factory(),
            'lead_id' => null,
            'trigger' => AutomationTrigger::LeadCreated,
            'status' => AutomationRunStatus::Skipped,
            'dry_run' => false,
            'context' => [],
            'result' => ['message' => 'Automation engine is not implemented yet.'],
        ];
    }

    public function tested(): static
    {
        return $this->state(fn (): array => [
            'status' => AutomationRunStatus::Tested,
            'dry_run' => true,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Enums\AutomationActionType;
use App\Models\Automation;
use App\Models\AutomationAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AutomationAction>
 */
class AutomationActionFactory extends Factory
{
    protected $model = AutomationAction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'automation_id' => Automation::factory(),
            'type' => AutomationActionType::CreateTask,
            'config' => [],
            'sort_order' => 0,
        ];
    }
}

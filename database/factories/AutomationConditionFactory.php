<?php

namespace Database\Factories;

use App\Enums\AutomationConditionField;
use App\Enums\AutomationConditionOperator;
use App\Enums\LeadStatus;
use App\Models\Automation;
use App\Models\AutomationCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AutomationCondition>
 */
class AutomationConditionFactory extends Factory
{
    protected $model = AutomationCondition::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'automation_id' => Automation::factory(),
            'field' => AutomationConditionField::Status,
            'operator' => AutomationConditionOperator::Equals,
            'value' => ['text' => LeadStatus::New->value],
            'sort_order' => 0,
        ];
    }
}

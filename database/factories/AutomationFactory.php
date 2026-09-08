<?php

namespace Database\Factories;

use App\Enums\AutomationTrigger;
use App\Models\Automation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Automation>
 */
class AutomationFactory extends Factory
{
    protected $model = Automation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'is_active' => false,
            'trigger' => null,
            'last_run_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'is_active' => true,
            'trigger' => AutomationTrigger::LeadCreated,
        ]);
    }

    public function withTrigger(AutomationTrigger $trigger = AutomationTrigger::LeadCreated): static
    {
        return $this->state(fn (): array => [
            'trigger' => $trigger,
        ]);
    }
}

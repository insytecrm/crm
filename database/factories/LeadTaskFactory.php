<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\Lead;
use App\Models\LeadTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadTask>
 */
class LeadTaskFactory extends Factory
{
    protected $model = LeadTask::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = User::query()->value('id');

        return [
            'lead_id' => Lead::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'due_at' => fake()->optional()->dateTimeBetween('now', '+2 weeks'),
            'status' => TaskStatus::Pending,
            'completed_at' => null,
            'assigned_to_id' => $userId,
            'created_by_id' => $userId,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (): array => [
            'status' => TaskStatus::InProgress,
            'completed_at' => null,
        ]);
    }

    public function complete(): static
    {
        return $this->state(fn (): array => [
            'status' => TaskStatus::Complete,
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => TaskStatus::Cancelled,
            'completed_at' => null,
        ]);
    }
}

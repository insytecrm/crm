<?php

namespace Database\Factories;

use App\Models\SalesTeam;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesTeam>
 */
class SalesTeamFactory extends Factory
{
    protected $model = SalesTeam::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'manager_id' => null,
            'is_active' => true,
            'created_by_id' => null,
        ];
    }

    public function withManager(?User $manager = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'manager_id' => $manager?->id ?? User::factory(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}

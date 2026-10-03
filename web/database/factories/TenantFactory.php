<?php

namespace Database\Factories;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => 't'.Str::lower(fake()->unique()->bothify('????????')),
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'status' => TenantStatus::Active,
            'tenancy_create_database' => false,
        ];
    }

    /**
     * Provision a real tenant database when the tenant is created.
     */
    public function withDatabase(): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenancy_create_database' => true,
        ]);
    }

    /**
     * Indicate that the company account is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => TenantStatus::Suspended,
        ]);
    }
}

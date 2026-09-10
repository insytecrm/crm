<?php

namespace Database\Factories;

use App\Enums\PlatformLeadSource;
use App\Enums\PlatformLeadStage;
use App\Models\PlatformLead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformLead>
 */
class PlatformLeadFactory extends Factory
{
    protected $model = PlatformLead::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'contact_person' => fake()->name(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->numerify('+91 ##### #####'),
            'location' => fake()->city(),
            'source' => fake()->randomElement(PlatformLeadSource::cases()),
            'stage' => PlatformLeadStage::NewLead,
            'owner_id' => User::factory()->superAdmin(),
            'next_action_label' => null,
            'next_action_at' => null,
            'demo_date' => null,
            'demo_time' => null,
            'tenant_id' => null,
            'created_by_id' => null,
        ];
    }

    public function demoScheduled(): static
    {
        return $this->state(fn (): array => [
            'stage' => PlatformLeadStage::DemoScheduled,
            'demo_date' => now()->addDays(3)->toDateString(),
            'demo_time' => '11:00:00',
            'next_action_label' => __('Demo scheduled'),
            'next_action_at' => now()->addDays(3)->setTime(11, 0),
        ]);
    }
}

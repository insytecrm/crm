<?php

namespace Database\Factories;

use App\Enums\LeadBudget;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\PropertyType;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = User::query()->value('id');

        return [
            'name' => fake()->name(),
            'phone' => fake()->numerify('+91 ##########'),
            'email' => fake()->safeEmail(),
            'source' => fake()->randomElement([
                LeadSource::Referral->value,
                LeadSource::WalkIn->value,
                LeadSource::Facebook->value,
                LeadSource::GoogleSheets->value,
                LeadSource::Api->value,
            ]),
            'sub_source' => fake()->optional()->words(2, true),
            'budget' => fake()->randomElement(LeadBudget::cases()),
            'location' => fake()->city(),
            'property_type' => fake()->randomElement(PropertyType::cases()),
            'configuration' => fake()->randomElement(['1 BHK', '2 BHK', '3 BHK', '4 BHK']),
            'assigned_to_id' => $userId,
            'status' => LeadStatus::New,
            'lead_score' => fake()->numberBetween(0, 100),
            'next_follow_up_at' => fake()->optional()->dateTimeBetween('-1 week', '+2 weeks'),
            'upcoming_site_visit_at' => fake()->optional()->dateTimeBetween('now', '+1 month'),
            'next_action' => fake()->optional()->sentence(),
            'last_activity_at' => now(),
            'created_by_id' => $userId,
        ];
    }

    public function status(LeadStatus $status): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => $status,
        ]);
    }

    public function followUpDue(): static
    {
        return $this
            ->state(fn (array $attributes): array => [
                'status' => LeadStatus::FollowUp,
                'next_follow_up_at' => now()->subHour(),
            ])
            ->afterCreating(function (Lead $lead): void {
                LeadScheduledEvent::factory()->create([
                    'lead_id' => $lead->id,
                    'type' => LeadScheduledEventType::FollowUp,
                    'sequence_number' => 1,
                    'scheduled_at' => $lead->next_follow_up_at,
                    'status' => LeadScheduledEventStatus::Scheduled,
                ]);
            });
    }

    public function priority(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => LeadStatus::Qualified,
            'lead_score' => Lead::PRIORITY_SCORE_THRESHOLD,
        ]);
    }

    public function converted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => LeadStatus::Converted,
            'closed_at' => now(),
        ]);
    }

    public function lost(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => LeadStatus::Lost,
            'closed_at' => now(),
        ]);
    }

    public function unassigned(): static
    {
        return $this->state(fn (array $attributes): array => [
            'assigned_to_id' => null,
        ]);
    }
}

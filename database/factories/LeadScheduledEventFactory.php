<?php

namespace Database\Factories;

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\ScheduledActivityPriority;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadScheduledEvent>
 */
class LeadScheduledEventFactory extends Factory
{
    protected $model = LeadScheduledEvent::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'type' => LeadScheduledEventType::FollowUp,
            'sequence_number' => 1,
            'scheduled_at' => now()->addDay(),
            'priority' => ScheduledActivityPriority::Normal,
            'notes' => fake()->optional()->sentence(),
            'status' => LeadScheduledEventStatus::Scheduled,
            'completed_at' => null,
            'user_id' => User::query()->value('id'),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => LeadScheduledEventStatus::Completed,
            'completed_at' => now(),
        ]);
    }
}

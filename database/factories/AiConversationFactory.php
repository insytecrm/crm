<?php

namespace Database\Factories;

use App\Enums\AiConversationStatus;
use App\Models\AiConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiConversation>
 */
class AiConversationFactory extends Factory
{
    protected $model = AiConversation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'status' => AiConversationStatus::Open,
            'last_message_at' => now(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => AiConversationStatus::Completed,
        ]);
    }
}

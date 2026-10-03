<?php

namespace Database\Factories;

use App\Models\TeamConversation;
use App\Models\TeamMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamMessage>
 */
class TeamMessageFactory extends Factory
{
    protected $model = TeamMessage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_conversation_id' => TeamConversation::factory(),
            'user_id' => 1,
            'body' => fake()->sentence(),
            'attachments' => null,
        ];
    }
}

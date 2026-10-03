<?php

namespace Database\Factories;

use App\Models\AiConversation;
use App\Models\AiMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiMessage>
 */
class AiMessageFactory extends Factory
{
    protected $model = AiMessage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ai_conversation_id' => AiConversation::factory(),
            'role' => 'user',
            'content' => fake()->sentence(),
            'actions' => null,
        ];
    }

    public function assistant(): static
    {
        return $this->state(fn (): array => [
            'role' => 'assistant',
        ]);
    }
}

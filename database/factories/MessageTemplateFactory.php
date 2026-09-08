<?php

namespace Database\Factories;

use App\Enums\MessageTemplateChannel;
use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MessageTemplate>
 */
class MessageTemplateFactory extends Factory
{
    protected $model = MessageTemplate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by_id' => User::factory(),
            'name' => fake()->words(3, true),
            'channel' => MessageTemplateChannel::WhatsApp,
            'subject' => null,
            'body' => 'Hi {{lead.name}}, this is {{user.name}} from {{company.name}}.',
            'is_active' => true,
        ];
    }

    public function email(): static
    {
        return $this->state(fn (): array => [
            'channel' => MessageTemplateChannel::Email,
            'subject' => 'Following up, {{lead.name}}',
        ]);
    }

    public function sms(): static
    {
        return $this->state(fn (): array => [
            'channel' => MessageTemplateChannel::Sms,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}

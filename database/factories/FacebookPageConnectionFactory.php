<?php

namespace Database\Factories;

use App\Enums\FacebookPageConnectionStatus;
use App\Models\FacebookPageConnection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FacebookPageConnection>
 */
class FacebookPageConnectionFactory extends Factory
{
    protected $model = FacebookPageConnection::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by_id' => User::factory(),
            'page_id' => (string) fake()->numerify('##############'),
            'page_name' => null,
            'status' => FacebookPageConnectionStatus::Draft,
            'page_access_token' => 'fake-page-token',
            'campaigns' => null,
            'lead_forms' => null,
            'selected_form_ids' => null,
            'form_fields' => null,
            'field_map' => null,
            'total_synced' => 0,
            'total_skipped' => 0,
            'total_failed' => 0,
            'verified_at' => null,
            'connected_at' => null,
            'last_lead_at' => null,
            'last_error' => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => FacebookPageConnectionStatus::Verified,
            'page_name' => 'Demo Facebook Page',
            'campaigns' => [
                ['id' => 'camp_1', 'name' => 'Spring Launch', 'status' => 'ACTIVE'],
            ],
            'lead_forms' => [
                [
                    'id' => 'form_1',
                    'name' => 'Website Leads',
                    'status' => 'ACTIVE',
                    'questions' => [
                        ['key' => 'full_name', 'label' => 'Full Name'],
                        ['key' => 'phone_number', 'label' => 'Phone Number'],
                        ['key' => 'email', 'label' => 'Email'],
                    ],
                ],
            ],
            'form_fields' => [
                ['key' => 'full_name', 'label' => 'Full Name'],
                ['key' => 'phone_number', 'label' => 'Phone Number'],
                ['key' => 'email', 'label' => 'Email'],
            ],
            'verified_at' => now(),
        ]);
    }

    public function connected(): static
    {
        return $this->verified()->state(fn (): array => [
            'status' => FacebookPageConnectionStatus::Connected,
            'selected_form_ids' => ['form_1'],
            'field_map' => [
                'name' => 'full_name',
                'phone' => 'phone_number',
                'email' => 'email',
            ],
            'connected_at' => now(),
        ]);
    }

    public function paused(): static
    {
        return $this->connected()->state(fn (): array => [
            'status' => FacebookPageConnectionStatus::Paused,
        ]);
    }
}

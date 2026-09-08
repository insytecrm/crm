<?php

namespace Database\Factories;

use App\Models\FacebookPageRegistration;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FacebookPageRegistration>
 */
class FacebookPageRegistrationFactory extends Factory
{
    protected $model = FacebookPageRegistration::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::query()->value('id') ?? 'acme',
            'page_id' => (string) fake()->numerify('##############'),
            'is_active' => true,
        ];
    }
}

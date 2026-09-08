<?php

namespace Database\Factories;

use App\Enums\PropertyPortal;
use App\Models\PortalWebhookEndpoint;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PortalWebhookEndpoint>
 */
class PortalWebhookEndpointFactory extends Factory
{
    protected $model = PortalWebhookEndpoint::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $secret = 'whsec_'.Str::random(40);

        return [
            'tenant_id' => Tenant::factory(),
            'portal' => fake()->randomElement(PropertyPortal::cases()),
            'webhook_id' => 'wh_'.Str::lower(Str::random(24)),
            'secret_hash' => hash('sha256', $secret),
            'secret_encrypted' => $secret,
            'secret_last_four' => substr($secret, -4),
            'is_active' => true,
            'generated_at' => now(),
            'last_used_at' => null,
        ];
    }
}

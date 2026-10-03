<?php

namespace App\Actions;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;

class CreateTenant
{
    /**
     * @param  array{
     *     slug: string,
     *     name: string,
     *     email?: string|null,
     *     status?: string,
     *     admin_name: string,
     *     admin_email: string,
     *     admin_password: string,
     *     owner_name?: string|null,
     *     phone?: string|null,
     *     location?: string|null,
     *     plan_key?: string|null,
     *     billing_cycle?: string|null,
     *     trial_days?: int|null
     * }  $data
     */
    public function handle(array $data): Tenant
    {
        $attributes = [
            'id' => $data['slug'],
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'status' => $data['status'] ?? TenantStatus::Active->value,
        ];

        foreach (['owner_name', 'phone', 'location', 'plan_key', 'billing_cycle', 'trial_days'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                $attributes[$key] = $data[$key];
            }
        }

        $tenant = Tenant::create($attributes);

        $tenant->run(function () use ($data): void {
            $user = User::query()->create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => $data['admin_password'],
                'email_verified_at' => now(),
            ]);

            app(ProvisionTenantRoles::class)->handle($user);
        });

        return $tenant;
    }
}

<?php

namespace App\Actions;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;

class CreateTenant
{
    /**
     * @param  array{slug: string, name: string, email?: string|null, status?: string, admin_name: string, admin_email: string, admin_password: string}  $data
     */
    public function handle(array $data): Tenant
    {
        $tenant = Tenant::create([
            'id' => $data['slug'],
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'status' => $data['status'] ?? TenantStatus::Active->value,
        ]);

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

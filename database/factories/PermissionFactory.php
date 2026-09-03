<?php

namespace Database\Factories;

use App\Enums\TenantPermission;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $permission = fake()->randomElement(TenantPermission::cases());

        return [
            'key' => $permission->value,
            'label' => $permission->label(),
            'group' => $permission->group(),
            'description' => $permission->description(),
            'sort_order' => 0,
        ];
    }
}

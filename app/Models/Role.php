<?php

namespace App\Models;

use App\Enums\TenantPermission;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'description',
    'is_system',
    'is_active',
])]
class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function isAdministrator(): bool
    {
        return $this->slug === 'administrator';
    }

    public function grants(TenantPermission|string $permission): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isAdministrator()) {
            return true;
        }

        $key = $permission instanceof TenantPermission ? $permission->value : $permission;

        if ($this->relationLoaded('permissions')) {
            return $this->permissions->contains('key', $key);
        }

        return $this->permissions()->where('key', $key)->exists();
    }
}

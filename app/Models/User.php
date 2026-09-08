<?php

namespace App\Models;

use App\Contracts\DataTableDefinition;
use App\Enums\LeadListingFilter;
use App\Enums\TenantPermission;
use App\Support\DataTable\TablePreferencesSupport;
use App\Support\LeadTablePreferences;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'is_super_admin', 'role_id', 'is_active', 'preferences'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'is_active' => 'boolean',
            'preferences' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * @return HasMany<SalesTeam, $this>
     */
    public function managedTeams(): HasMany
    {
        return $this->hasMany(SalesTeam::class, 'manager_id');
    }

    /**
     * @return BelongsToMany<SalesTeam, $this>
     */
    public function salesTeams(): BelongsToMany
    {
        return $this->belongsToMany(SalesTeam::class)->withTimestamps();
    }

    public function isManagerRole(): bool
    {
        $this->loadMissing('role');

        return in_array($this->role?->slug, ['manager', 'administrator'], true);
    }

    public function hasPermission(TenantPermission|string $permission): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        $this->loadMissing('role.permissions');

        if ($this->role === null) {
            return false;
        }

        return $this->role->grants($permission);
    }

    public function isActive(): bool
    {
        return (bool) ($this->is_active ?? true);
    }

    public function isAdministrator(): bool
    {
        return $this->role?->isAdministrator() ?? false;
    }

    /**
     * @return array{
     *     columns: array<string, bool>,
     *     actions: array<string, bool>,
     * }
     */
    public function leadTablePreferences(LeadListingFilter $listing = LeadListingFilter::All): array
    {
        $preferences = $this->preferences ?? [];
        $saved = $preferences[LeadTablePreferences::StorageKey] ?? null;

        return LeadTablePreferences::resolveForListing(
            is_array($saved) ? $saved : null,
            $listing,
        );
    }

    /**
     * @return array{
     *     columns: array<string, bool>,
     *     custom_columns: list<array{
     *         key: string,
     *         label: string,
     *         type: string,
     *         options: list<string>,
     *         visible: bool,
     *     }>,
     * }
     */
    public function dataTablePreferences(
        string $tableKey,
        DataTableDefinition $definition,
        ?string $listingKey = null,
    ): array {
        $preferences = $this->preferences ?? [];
        $saved = $preferences[TablePreferencesSupport::storageKey($tableKey)] ?? null;

        if ($listingKey !== null && TablePreferencesSupport::usesListings($tableKey)) {
            return TablePreferencesSupport::resolveForListing(
                $definition,
                is_array($saved) ? $saved : null,
                $listingKey,
            );
        }

        return TablePreferencesSupport::resolve($definition, is_array($saved) ? $saved : null);
    }
}

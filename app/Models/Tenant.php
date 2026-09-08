<?php

namespace App\Models;

use App\Enums\DomainPurpose;
use App\Enums\TenantStatus;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    /** @use HasFactory<TenantFactory> */
    use HasDatabase, HasDomains, HasFactory;

    /**
     * @var list<string>
     */
    protected $hidden = [
        'lead_api_token_hash',
        'lead_api_token_encrypted',
    ];

    /**
     * Path segments that cannot be used as a company slug.
     *
     * @var list<string>
     */
    public const ReservedIds = [
        'platform',
        'up',
        'livewire',
        'storage',
        'build',
        'vendor',
        'horizon',
        'telescope',
        'boost',
    ];

    /**
     * @return list<string>
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'status',
            'lead_api_token_hash',
            'lead_api_token_encrypted',
            'lead_api_token_last_four',
            'lead_api_token_generated_at',
            'lead_api_token_last_used_at',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
            'lead_api_token_encrypted' => 'encrypted',
            'lead_api_token_generated_at' => 'datetime',
            'lead_api_token_last_used_at' => 'datetime',
        ];
    }

    public function domainFor(DomainPurpose $purpose): ?Domain
    {
        /** @var Domain|null $domain */
        $domain = $this->domains()
            ->where('purpose', $purpose->value)
            ->first();

        return $domain;
    }

    public function verifiedDomainFor(DomainPurpose $purpose): ?Domain
    {
        $domain = $this->domainFor($purpose);

        return $domain?->isVerified() === true ? $domain : null;
    }

    public function canPublishMicrosites(): bool
    {
        $website = $this->domainFor(DomainPurpose::Website);

        if ($website === null) {
            return true;
        }

        return $website->isVerified();
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWherePlanKey(Builder $query, string $planKey): Builder
    {
        return $query->where($this->getColumnForQuery('plan_key'), $planKey);
    }

    /**
     * @return HasMany<PartnerSubscription, $this>
     */
    public function partnerSubscriptions(): HasMany
    {
        return $this->hasMany(PartnerSubscription::class);
    }

    /**
     * @return HasMany<PortalWebhookEndpoint, $this>
     */
    public function portalWebhookEndpoints(): HasMany
    {
        return $this->hasMany(PortalWebhookEndpoint::class);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->partnerSubscriptions()->active()->exists();
    }
}

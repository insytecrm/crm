<?php

namespace App\Models;

use App\Enums\DomainPurpose;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class Domain extends BaseDomain
{
    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'purpose' => DomainPurpose::class,
            'verified_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function txtRecordName(): string
    {
        return '@';
    }

    public function txtRecordValue(): string
    {
        return config('domains.verification_prefix').$this->verification_token;
    }

    public function cnameTarget(): string
    {
        return (string) config('domains.cname_target');
    }

    public function accessUrl(): string
    {
        $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';

        return $scheme.'://'.$this->domain;
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopePurpose(Builder $query, DomainPurpose $purpose): Builder
    {
        return $query->where('purpose', $purpose->value);
    }
}

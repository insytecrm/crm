<?php

namespace App\Actions;

use App\Enums\DomainPurpose;
use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UpsertTenantDomain
{
    public function handle(Tenant $tenant, DomainPurpose $purpose, string $hostname): Domain
    {
        $hostname = $this->normalize($hostname);

        $occupied = Domain::query()
            ->where('domain', $hostname)
            ->where('tenant_id', '!=', $tenant->id)
            ->exists();

        if ($occupied) {
            throw ValidationException::withMessages([
                'domain' => __('This domain is already connected to another workspace.'),
            ]);
        }

        /** @var Domain|null $existing */
        $existing = $tenant->domainFor($purpose);

        if ($existing !== null && $existing->domain === $hostname && $existing->isVerified()) {
            return $existing;
        }

        if ($existing !== null) {
            $existing->fill([
                'domain' => $hostname,
                'verification_token' => $existing->verification_token ?: Str::random(40),
                'verified_at' => null,
            ]);
            $existing->save();

            return $existing->refresh();
        }

        /** @var Domain $domain */
        $domain = $tenant->createDomain([
            'domain' => $hostname,
            'purpose' => $purpose->value,
            'verification_token' => Str::random(40),
            'verified_at' => null,
        ]);

        return $domain;
    }

    private function normalize(string $hostname): string
    {
        $hostname = strtolower(trim($hostname));
        $hostname = preg_replace('#^https?://#', '', $hostname) ?? $hostname;
        $hostname = explode('/', $hostname)[0];
        $hostname = rtrim($hostname, '.');

        return $hostname;
    }
}

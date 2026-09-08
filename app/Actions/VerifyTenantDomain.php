<?php

namespace App\Actions;

use App\Contracts\DnsRecordVerifier;
use App\Models\Domain;
use Illuminate\Validation\ValidationException;

class VerifyTenantDomain
{
    public function __construct(private DnsRecordVerifier $dnsRecordVerifier) {}

    public function handle(Domain $domain): Domain
    {
        if ($domain->isVerified()) {
            return $domain;
        }

        $expected = $domain->txtRecordValue();
        $values = $this->dnsRecordVerifier->txtValues($domain->domain);

        $matched = collect($values)->contains(
            fn (string $value): bool => str_contains($value, $expected) || $value === $expected,
        );

        if (! $matched) {
            throw ValidationException::withMessages([
                'domain' => __('We could not find the required TXT record yet. DNS changes can take a few minutes to propagate.'),
            ]);
        }

        $domain->forceFill([
            'verified_at' => now(),
        ])->save();

        return $domain->refresh();
    }
}

<?php

namespace App\Support\Dns;

use App\Contracts\DnsRecordVerifier;

class FakeDnsRecordVerifier implements DnsRecordVerifier
{
    /**
     * @param  array<string, list<string>>  $records
     */
    public function __construct(private array $records = []) {}

    /**
     * @param  list<string>  $values
     */
    public function setTxt(string $host, array $values): void
    {
        $this->records[strtolower($host)] = $values;
    }

    /**
     * @return list<string>
     */
    public function txtValues(string $host): array
    {
        return $this->records[strtolower($host)] ?? [];
    }
}

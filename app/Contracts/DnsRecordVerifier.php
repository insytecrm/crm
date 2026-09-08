<?php

namespace App\Contracts;

interface DnsRecordVerifier
{
    /**
     * @return list<string>
     */
    public function txtValues(string $host): array;
}

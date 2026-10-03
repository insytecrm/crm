<?php

namespace App\Support\Dns;

use App\Contracts\DnsRecordVerifier;

class PhpDnsRecordVerifier implements DnsRecordVerifier
{
    /**
     * @return list<string>
     */
    public function txtValues(string $host): array
    {
        $records = @dns_get_record($host, DNS_TXT);

        if (! is_array($records)) {
            return [];
        }

        $values = [];

        foreach ($records as $record) {
            $txt = $record['txt'] ?? null;

            if (is_string($txt) && $txt !== '') {
                $values[] = $txt;
            }
        }

        return $values;
    }
}

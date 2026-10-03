<?php

namespace App\Actions;

use App\Models\Tenant;
use Illuminate\Support\Str;

class IssueTenantLeadApiToken
{
    public function handle(Tenant $tenant): string
    {
        $plainTextToken = 'crm_'.Str::random(48);

        $tenant->update([
            'lead_api_token_hash' => hash('sha256', $plainTextToken),
            'lead_api_token_encrypted' => $plainTextToken,
            'lead_api_token_last_four' => substr($plainTextToken, -4),
            'lead_api_token_generated_at' => now(),
            'lead_api_token_last_used_at' => null,
        ]);

        return $plainTextToken;
    }
}

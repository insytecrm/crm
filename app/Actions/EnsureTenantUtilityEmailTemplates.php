<?php

namespace App\Actions;

use App\Enums\TenantUtilityEmailTemplateType;
use App\Models\UtilityEmailTemplate;

class EnsureTenantUtilityEmailTemplates
{
    public function handle(): void
    {
        foreach (TenantUtilityEmailTemplateType::cases() as $type) {
            $defaults = $type->defaults();

            UtilityEmailTemplate::query()->firstOrCreate(
                ['type' => $type->value],
                [
                    'subject' => $defaults['subject'],
                    'body' => $defaults['body'],
                    'is_active' => true,
                ],
            );
        }
    }
}

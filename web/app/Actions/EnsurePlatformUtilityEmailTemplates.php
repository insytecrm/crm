<?php

namespace App\Actions;

use App\Enums\PlatformUtilityEmailTemplateType;
use App\Models\PlatformUtilityEmailTemplate;

class EnsurePlatformUtilityEmailTemplates
{
    public function handle(): void
    {
        foreach (PlatformUtilityEmailTemplateType::cases() as $type) {
            $defaults = $type->defaults();

            PlatformUtilityEmailTemplate::query()->firstOrCreate(
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

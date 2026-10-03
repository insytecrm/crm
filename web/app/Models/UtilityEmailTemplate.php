<?php

namespace App\Models;

use App\Enums\TenantUtilityEmailTemplateType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'type',
    'subject',
    'body',
    'is_active',
])]
class UtilityEmailTemplate extends Model
{
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'type' => TenantUtilityEmailTemplateType::class,
            'is_active' => 'boolean',
        ];
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}

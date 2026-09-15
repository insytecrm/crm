<?php

namespace App\Models;

use App\Enums\PlatformUtilityEmailTemplateType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

#[Fillable([
    'type',
    'subject',
    'body',
    'is_active',
])]
class PlatformUtilityEmailTemplate extends Model
{
    use CentralConnection;

    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'type' => PlatformUtilityEmailTemplateType::class,
            'is_active' => 'boolean',
        ];
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}

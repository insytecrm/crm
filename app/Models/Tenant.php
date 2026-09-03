<?php

namespace App\Models;

use App\Enums\TenantStatus;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    /** @use HasFactory<TenantFactory> */
    use HasDatabase, HasFactory;

    /**
     * Path segments that cannot be used as a company slug.
     *
     * @var list<string>
     */
    public const ReservedIds = [
        'platform',
        'up',
        'livewire',
        'storage',
        'build',
        'vendor',
        'horizon',
        'telescope',
        'boost',
    ];

    /**
     * @return list<string>
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'status',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
        ];
    }
}

<?php

namespace App\Models;

use App\Enums\PlanFeature;
use App\Enums\PlanPack;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

#[Fillable([
    'module',
    'pack',
    'capabilities',
])]
class PlanPackPreset extends Model
{
    use CentralConnection;

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'module' => PlanFeature::class,
            'pack' => PlanPack::class,
            'capabilities' => 'array',
        ];
    }

    /**
     * @return list<string>
     */
    public static function capabilityKeys(PlanFeature $feature, PlanPack $pack): array
    {
        $preset = static::query()
            ->where('module', $feature->value)
            ->where('pack', $pack->value)
            ->first();

        if ($preset === null) {
            return [];
        }

        return array_values(array_filter(
            $preset->capabilities ?? [],
            fn (mixed $key): bool => is_string($key) && $key !== '',
        ));
    }
}

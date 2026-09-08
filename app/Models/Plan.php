<?php

namespace App\Models;

use App\Enums\PlanFeature;
use App\Enums\PlanPack;
use App\Enums\PlanStatus;
use App\Support\Platform\PlanDefinitionCatalog;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

#[Fillable([
    'key',
    'name',
    'description',
    'status',
    'price_monthly',
    'price_annual',
    'currency',
    'trial_enabled',
    'trial_days',
    'features',
    'packs',
    'capabilities',
    'limits',
])]
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use CentralConnection, HasFactory;

    protected $attributes = [
        'status' => 'active',
        'currency' => 'INR',
        'trial_enabled' => true,
        'trial_days' => 7,
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => PlanStatus::class,
            'price_monthly' => 'integer',
            'price_annual' => 'integer',
            'trial_enabled' => 'boolean',
            'trial_days' => 'integer',
            'features' => 'array',
            'packs' => 'array',
            'capabilities' => 'array',
            'limits' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'key';
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PlanStatus::Active);
    }

    public function isArchived(): bool
    {
        return $this->status === PlanStatus::Archived;
    }

    public function monthlyPriceLabel(): string
    {
        return $this->moneyLabel($this->price_monthly);
    }

    public function annualPriceLabel(): string
    {
        return $this->moneyLabel($this->price_annual);
    }

    public function trialLabel(): string
    {
        if (! $this->trial_enabled) {
            return __('No trial');
        }

        return trans_choice(':count day|:count days', $this->trial_days, [
            'count' => $this->trial_days,
        ]);
    }

    public function hasFeature(PlanFeature|string $feature): bool
    {
        $key = $feature instanceof PlanFeature ? $feature->value : $feature;

        return (bool) ($this->features[$key] ?? false);
    }

    public function packFor(PlanFeature $feature): PlanPack
    {
        if (! $this->hasFeature($feature)) {
            return PlanPack::Off;
        }

        $value = $this->packs[$feature->value] ?? null;

        return PlanPack::tryFrom(is_string($value) ? $value : '')
            ?? ($feature->isPackable() ? PlanPack::Basic : PlanPack::Advanced);
    }

    /**
     * @return list<string>
     */
    public function resolvedCapabilityKeys(): array
    {
        return PlanDefinitionCatalog::resolveCapabilityKeys(
            $this->features ?? [],
            $this->packs ?? [],
            $this->capabilities ?? [],
        );
    }

    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->resolvedCapabilityKeys(), true);
    }

    /**
     * @return int|null Null means unlimited.
     */
    public function limitFor(string $key): ?int
    {
        $value = $this->limits[$key] ?? null;

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    public function activePartnersCount(): int
    {
        return Tenant::query()
            ->wherePlanKey($this->key)
            ->count();
    }

    public static function uniqueKeyFromName(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name, '');
        $base = preg_replace('/^[0-9]+/', '', $base) ?: 'plan';
        $base = Str::limit($base, 32, '');

        $key = $base;
        $suffix = 1;

        while (
            static::query()
                ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
                ->where('key', $key)
                ->exists()
        ) {
            $key = Str::limit($base, 28, '').$suffix;
            $suffix++;
        }

        return $key;
    }

    private function moneyLabel(int $amount): string
    {
        return '₹'.number_format($amount);
    }
}

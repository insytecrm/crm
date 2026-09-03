<?php

namespace App\Models;

use App\Casts\FlexibleEnumCast;
use App\Enums\ProjectStatus;
use App\Enums\PropertyType;
use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'developer_name',
    'project_name',
    'project_location',
    'rera_number',
    'property_type',
    'project_status',
    'possession_date',
    'total_land_parcel_acres',
    'total_towers',
    'total_floors',
    'carpet_area_from_sqft',
    'carpet_area_to_sqft',
    'price_from',
    'price_to',
    'tagging_period_days',
    'payout_percent',
    'sourcing_manager_name',
    'sourcing_manager_contact',
    'amenities',
    'configurations',
    'layout_files',
    'brochure_files',
    'created_by_id',
])]
class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'property_type' => FlexibleEnumCast::class.':'.PropertyType::class,
            'project_status' => FlexibleEnumCast::class.':'.ProjectStatus::class,
            'total_land_parcel_acres' => 'decimal:2',
            'total_towers' => 'integer',
            'carpet_area_from_sqft' => 'integer',
            'carpet_area_to_sqft' => 'integer',
            'price_from' => 'integer',
            'price_to' => 'integer',
            'tagging_period_days' => 'integer',
            'payout_percent' => 'decimal:2',
            'amenities' => 'array',
            'configurations' => 'array',
            'layout_files' => 'array',
            'brochure_files' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function listLabel(): string
    {
        return filled($this->developer_name)
            ? "{$this->project_name} · {$this->developer_name}"
            : $this->project_name;
    }

    /**
     * @return array<int, array{id: int, label: string, configurations: array<int, array{name: string, carpet_area_sqft: ?int, price: ?int, unit_count: ?int}>}>
     */
    public static function bookingFormOptions(): array
    {
        return static::query()
            ->orderBy('project_name')
            ->get(['id', 'project_name', 'developer_name', 'configurations'])
            ->map(fn (Property $property): array => [
                'id' => $property->id,
                'label' => $property->listLabel(),
                'configurations' => $property->configurations ?? [],
            ])
            ->values()
            ->all();
    }
}

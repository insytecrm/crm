<?php

namespace App\Models;

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\ScheduledActivityContactMethod;
use App\Enums\ScheduledActivityNextStep;
use App\Enums\ScheduledActivityOutcome;
use App\Enums\ScheduledActivityPriority;
use App\Enums\SiteVisitNextStep;
use App\Enums\SiteVisitOutcome;
use App\Enums\SiteVisitType;
use App\Support\Ordinal;
use BackedEnum;
use Database\Factories\LeadScheduledEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'lead_id',
    'property_id',
    'visit_type',
    'type',
    'sequence_number',
    'scheduled_at',
    'remind_at',
    'reminder_before_seconds',
    'reminder_dismissed_at',
    'rescheduled_at',
    'priority',
    'notes',
    'status',
    'completed_at',
    'attended',
    'completion_method',
    'completion_outcome',
    'next_step_type',
    'completion_notes',
    'user_id',
])]
class LeadScheduledEvent extends Model
{
    /** @use HasFactory<LeadScheduledEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'type' => LeadScheduledEventType::class,
            'status' => LeadScheduledEventStatus::class,
            'priority' => ScheduledActivityPriority::class,
            'visit_type' => SiteVisitType::class,
            'attended' => 'boolean',
            'completion_method' => ScheduledActivityContactMethod::class,
            'sequence_number' => 'integer',
            'scheduled_at' => 'datetime',
            'remind_at' => 'datetime',
            'reminder_before_seconds' => 'integer',
            'reminder_dismissed_at' => 'datetime',
            'rescheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return Attribute<BackedEnum|null, BackedEnum|string|null>
     */
    protected function completionOutcome(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?BackedEnum {
                if ($value === null || $value === '') {
                    return null;
                }

                return match ($this->type) {
                    LeadScheduledEventType::SiteVisit => SiteVisitOutcome::tryFrom($value),
                    default => ScheduledActivityOutcome::tryFrom($value),
                };
            },
            set: fn (BackedEnum|string|null $value): ?string => match (true) {
                $value instanceof BackedEnum => $value->value,
                default => $value,
            },
        );
    }

    /**
     * @return Attribute<BackedEnum|null, BackedEnum|string|null>
     */
    protected function nextStepType(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?BackedEnum {
                if ($value === null || $value === '') {
                    return null;
                }

                return match ($this->type) {
                    LeadScheduledEventType::SiteVisit => SiteVisitNextStep::tryFrom($value),
                    default => ScheduledActivityNextStep::tryFrom($value),
                };
            },
            set: fn (BackedEnum|string|null $value): ?string => match (true) {
                $value instanceof BackedEnum => $value->value,
                default => $value,
            },
        );
    }

    /**
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ordinalLabel(): string
    {
        if ($this->type === LeadScheduledEventType::FollowUp && $this->sequence_number === 1) {
            return __('Initial Contact');
        }

        $ordinal = Ordinal::for($this->sequence_number);

        return match ($this->type) {
            LeadScheduledEventType::FollowUp => __(':ordinal Follow-up', ['ordinal' => $ordinal]),
            LeadScheduledEventType::SiteVisit => $this->visit_type instanceof SiteVisitType
                ? __(':ordinal Site Visit · :type', [
                    'ordinal' => $ordinal,
                    'type' => $this->visit_type->label(),
                ])
                : __(':ordinal Site Visit', ['ordinal' => $ordinal]),
        };
    }

    public function attendedLabel(): ?string
    {
        if ($this->attended === null) {
            return null;
        }

        return $this->attended ? __('Yes') : __('No');
    }
}

<?php

namespace App\Models;

use App\Casts\FlexibleEnumCast;
use App\Enums\LeadBudget;
use App\Enums\LeadClosingReason;
use App\Enums\LeadLostReason;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\PropertyType;
use App\Models\Scopes\LeadVisibilityScope;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

#[Fillable([
    'name',
    'phone',
    'email',
    'source',
    'budget',
    'location',
    'property_type',
    'configuration',
    'assigned_to_id',
    'status',
    'lead_score',
    'next_follow_up_at',
    'upcoming_site_visit_at',
    'next_action',
    'last_activity_at',
    'closed_at',
    'closing_reason',
    'lost_reasons',
    'closing_notes',
    'created_by_id',
])]
class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory, SoftDeletes;

    public const PRIORITY_SCORE_THRESHOLD = 70;

    protected static function booted(): void
    {
        static::addGlobalScope(new LeadVisibilityScope);
    }

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'closing_reason' => LeadClosingReason::class,
            'budget' => FlexibleEnumCast::class.':'.LeadBudget::class,
            'property_type' => FlexibleEnumCast::class.':'.PropertyType::class,
            'lead_score' => 'integer',
            'next_follow_up_at' => 'datetime',
            'upcoming_site_visit_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'closed_at' => 'datetime',
            'lost_reasons' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * @return HasMany<LeadScheduledEvent, $this>
     */
    public function scheduledEvents(): HasMany
    {
        return $this->hasMany(LeadScheduledEvent::class)->orderBy('sequence_number');
    }

    /**
     * @return HasMany<LeadScheduledEvent, $this>
     */
    public function completedSiteVisitEvents(): HasMany
    {
        return $this->hasMany(LeadScheduledEvent::class)
            ->where('type', LeadScheduledEventType::SiteVisit)
            ->where('status', LeadScheduledEventStatus::Completed)
            ->whereNotNull('property_id')
            ->orderBy('completed_at')
            ->orderBy('sequence_number');
    }

    public function propertyInterestLabel(): ?string
    {
        $events = $this->relationLoaded('completedSiteVisitEvents')
            ? $this->completedSiteVisitEvents
            : $this->completedSiteVisitEvents()->with('property')->get();

        $labels = $events
            ->pluck('property')
            ->filter()
            ->unique('id')
            ->map(fn (Property $property): string => $property->listLabel())
            ->values();

        if ($labels->isEmpty()) {
            return null;
        }

        return $labels->implode(', ');
    }

    public function statusStageLabel(): ?string
    {
        return match ($this->status) {
            LeadStatus::SiteVisit => $this->scheduledActivityStageLabel(LeadScheduledEventType::SiteVisit),
            LeadStatus::FollowUp => $this->scheduledActivityStageLabel(LeadScheduledEventType::FollowUp),
            LeadStatus::New, LeadStatus::Contacted => $this->scheduledActivityStageLabel(LeadScheduledEventType::FollowUp),
            LeadStatus::Qualified => $this->scheduledActivityStageLabel(LeadScheduledEventType::SiteVisit)
                ?? $this->scheduledActivityStageLabel(LeadScheduledEventType::FollowUp),
            LeadStatus::Negotiation => filled($this->next_action) ? $this->next_action : __('In negotiation'),
            LeadStatus::Converted => $this->hasBooking() ? __('Booked') : __('Converted'),
            LeadStatus::Lost => $this->lostStageLabel(),
        };
    }

    private function scheduledActivityStageLabel(LeadScheduledEventType $type): ?string
    {
        $events = $this->eventsOfType($type);

        $scheduled = $events
            ->where('status', LeadScheduledEventStatus::Scheduled)
            ->sortByDesc('sequence_number')
            ->first();

        if ($scheduled instanceof LeadScheduledEvent) {
            $state = $scheduled->scheduled_at?->isPast() ? __('overdue') : __('scheduled');

            return __(':label :state', [
                'label' => $this->stageActivityLabel($scheduled),
                'state' => $state,
            ]);
        }

        $completed = $events
            ->where('status', LeadScheduledEventStatus::Completed)
            ->sortByDesc('sequence_number')
            ->first();

        if ($completed instanceof LeadScheduledEvent) {
            return __(':label done', [
                'label' => $this->stageActivityLabel($completed),
            ]);
        }

        return null;
    }

    private function stageActivityLabel(LeadScheduledEvent $event): string
    {
        if ($event->type === LeadScheduledEventType::SiteVisit && $event->visit_type !== null) {
            return $event->visit_type->label();
        }

        return $event->ordinalLabel();
    }

    /**
     * @return Collection<int, LeadScheduledEvent>
     */
    private function eventsOfType(LeadScheduledEventType $type): Collection
    {
        $events = $this->relationLoaded('scheduledEvents')
            ? $this->scheduledEvents
            : $this->scheduledEvents()->get();

        return $events->where('type', $type)->values();
    }

    private function lostStageLabel(): ?string
    {
        $reasons = collect($this->lost_reasons ?? [])
            ->map(fn (mixed $reason): ?string => LeadLostReason::tryFrom((string) $reason)?->label())
            ->filter()
            ->values();

        if ($reasons->isEmpty()) {
            return $this->closing_reason?->label();
        }

        return $reasons->first();
    }

    /**
     * @return HasMany<LeadActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest('created_at');
    }

    /**
     * @return HasMany<LeadTask, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(LeadTask::class)->latest();
    }

    /**
     * @return HasMany<LeadNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class)->latest();
    }

    /**
     * @return HasMany<LeadDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(LeadDocument::class)->latest();
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * @return HasOne<Booking, $this>
     */
    public function latestBooking(): HasOne
    {
        return $this->hasOne(Booking::class)->latestOfMany(['booking_date', 'id']);
    }

    public function hasBooking(): bool
    {
        if (isset($this->bookings_count)) {
            return $this->bookings_count > 0;
        }

        return $this->bookings()->exists();
    }

    public function isClosed(): bool
    {
        return $this->status->isClosed();
    }

    public function whatsAppUrl(): ?string
    {
        if (! $this->phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->phone);

        return $digits ? 'https://wa.me/'.$digits : null;
    }

    public function callUrl(): ?string
    {
        return $this->phone ? 'tel:'.$this->phone : null;
    }

    /**
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopePriority(Builder $query): Builder
    {
        $now = now();
        $endOfDay = $now->copy()->endOfDay();

        return $query
            ->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Lost])
            ->where(function (Builder $query) use ($now, $endOfDay): void {
                $query->where('lead_score', '>=', self::PRIORITY_SCORE_THRESHOLD)
                    ->orWhere(function (Builder $query) use ($now): void {
                        $query->whereNotNull('next_follow_up_at')
                            ->where('next_follow_up_at', '<=', $now);
                    })
                    ->orWhere(function (Builder $query) use ($endOfDay): void {
                        $query->whereNotNull('upcoming_site_visit_at')
                            ->where('upcoming_site_visit_at', '<=', $endOfDay);
                    });
            });
    }

    /**
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopeNewLeads(Builder $query): Builder
    {
        return $query->where('status', LeadStatus::New);
    }

    /**
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopeFollowUpDue(Builder $query): Builder
    {
        return $query
            ->whereNotNull('next_follow_up_at')
            ->where('next_follow_up_at', '<=', now())
            ->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Lost]);
    }

    /**
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopeSiteVisitsScheduled(Builder $query): Builder
    {
        return $query
            ->whereNotNull('upcoming_site_visit_at')
            ->where('upcoming_site_visit_at', '>', now());
    }

    /**
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_to_id');
    }

    /**
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopeConverted(Builder $query): Builder
    {
        return $query->where('status', LeadStatus::Converted);
    }

    /**
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopeLost(Builder $query): Builder
    {
        return $query->where('status', LeadStatus::Lost);
    }
}

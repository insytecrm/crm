<?php

namespace App\Models;

use App\Enums\PlatformLeadSource;
use App\Enums\PlatformLeadStage;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantStatus;
use Database\Factories\PlatformLeadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

#[Fillable([
    'company_name',
    'contact_person',
    'email',
    'phone',
    'location',
    'source',
    'stage',
    'owner_id',
    'next_action_label',
    'next_action_at',
    'demo_date',
    'demo_time',
    'tenant_id',
    'created_by_id',
])]
class PlatformLead extends Model
{
    /** @use HasFactory<PlatformLeadFactory> */
    use CentralConnection, HasFactory;

    protected $attributes = [
        'stage' => 'new_lead',
        'source' => 'website',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'source' => PlatformLeadSource::class,
            'stage' => PlatformLeadStage::class,
            'next_action_at' => 'datetime',
            'demo_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return HasMany<PlatformLeadNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(PlatformLeadNote::class)->latest('id');
    }

    /**
     * @return HasMany<PlatformLeadActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(PlatformLeadActivity::class)->latest('id');
    }

    /**
     * @return HasMany<Quotation, $this>
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class)->latest('id');
    }

    public function nextActionDisplay(): string
    {
        if (is_string($this->next_action_label) && $this->next_action_label !== '') {
            return $this->next_action_label;
        }

        if ($this->stage === PlatformLeadStage::DemoScheduled && $this->demo_date !== null) {
            $label = __('Demo :date', [
                'date' => $this->demo_date->timezone(config('app.timezone'))->format('d M'),
            ]);

            if ($this->demo_time !== null) {
                $time = Carbon::parse($this->demo_time)->format('g:i A');
                $label .= ' · '.$time;
            }

            return $label;
        }

        return '—';
    }

    public function hasLinkedAccount(): bool
    {
        return $this->tenant_id !== null && $this->tenant_id !== '';
    }

    public function accountStatusValue(): ?string
    {
        if (! $this->hasLinkedAccount()) {
            return null;
        }

        $tenant = $this->tenant;

        if ($tenant === null) {
            return null;
        }

        if ($tenant->status === TenantStatus::Suspended) {
            return TenantStatus::Suspended->value;
        }

        $subscription = $this->latestPartnerSubscription();

        if ($subscription?->status === SubscriptionStatus::Trial) {
            return SubscriptionStatus::Trial->value;
        }

        if ($subscription?->status === SubscriptionStatus::PastDue) {
            return SubscriptionStatus::PastDue->value;
        }

        return $tenant->status?->value ?? TenantStatus::Active->value;
    }

    public function latestPartnerSubscription(): ?PartnerSubscription
    {
        $tenant = $this->tenant;

        if ($tenant === null) {
            return null;
        }

        if ($tenant->relationLoaded('partnerSubscriptions')) {
            return $tenant->partnerSubscriptions->sortByDesc('id')->first();
        }

        return $tenant->partnerSubscriptions()->latest('id')->first();
    }

    /**
     * @return list<array{id: int, company_name: string, contact_person: string, email: string, phone: string|null, stage: string}>
     */
    public static function quotationSelectOptions(): array
    {
        return static::query()
            ->orderBy('company_name')
            ->limit(200)
            ->get(['id', 'company_name', 'contact_person', 'email', 'phone', 'stage'])
            ->map(fn (self $lead): array => [
                'id' => $lead->id,
                'company_name' => $lead->company_name,
                'contact_person' => $lead->contact_person,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'stage' => $lead->stage->label(),
            ])
            ->all();
    }
}

<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\BillingInvoiceStatus;
use Database\Factories\BillingInvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

#[Fillable([
    'number',
    'tenant_id',
    'partner_subscription_id',
    'plan_id',
    'billing_cycle',
    'period_start',
    'period_end',
    'subtotal',
    'discount_amount',
    'tax_amount',
    'total',
    'status',
    'issued_at',
    'due_at',
    'billed_to_name',
    'billed_to_contact',
    'billed_to_email',
    'sent_at',
    'paid_at',
    'payment_initiated_at',
])]
class BillingInvoice extends Model
{
    /** @use HasFactory<BillingInvoiceFactory> */
    use CentralConnection, HasFactory;

    protected $attributes = [
        'subtotal' => 0,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total' => 0,
        'status' => 'pending',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'billing_cycle' => BillingCycle::class,
            'subtotal' => 'integer',
            'discount_amount' => 'integer',
            'tax_amount' => 'integer',
            'total' => 'integer',
            'status' => BillingInvoiceStatus::class,
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
            'payment_initiated_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return BelongsTo<PartnerSubscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(PartnerSubscription::class, 'partner_subscription_id');
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return HasMany<BillingPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(BillingPayment::class);
    }

    /**
     * @return HasMany<BillingDiscount, $this>
     */
    public function discounts(): HasMany
    {
        return $this->hasMany(BillingDiscount::class);
    }

    public static function nextNumber(): string
    {
        $latest = static::query()
            ->orderByDesc('id')
            ->value('number');

        $sequence = 1000;

        if (is_string($latest) && preg_match('/(\d+)$/', $latest, $matches) === 1) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return 'INV-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}

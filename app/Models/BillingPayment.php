<?php

namespace App\Models;

use App\Enums\BillingPaymentStatus;
use App\Enums\BillingPaymentType;
use Database\Factories\BillingPaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

#[Fillable([
    'tenant_id',
    'billing_invoice_id',
    'partner_subscription_id',
    'plan_id',
    'amount',
    'type',
    'status',
    'payment_method',
    'transaction_id',
    'payment_date',
    'initiated_at',
    'failed_at',
    'retried_at',
    'reminder_sent_at',
    'gateway',
    'gateway_transaction_id',
    'response_code',
    'failure_reason',
    'webhook_status',
])]
class BillingPayment extends Model
{
    /** @use HasFactory<BillingPaymentFactory> */
    use CentralConnection, HasFactory;

    protected $attributes = [
        'type' => 'subscription',
        'status' => 'pending',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'type' => BillingPaymentType::class,
            'status' => BillingPaymentStatus::class,
            'payment_date' => 'datetime',
            'initiated_at' => 'datetime',
            'failed_at' => 'datetime',
            'retried_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return BelongsTo<BillingInvoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'billing_invoice_id');
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
     * @return HasMany<BillingRefund, $this>
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(BillingRefund::class);
    }
}

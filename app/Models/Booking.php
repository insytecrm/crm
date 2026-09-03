<?php

namespace App\Models;

use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'property_id',
    'lead_id',
    'configuration_index',
    'configuration_name',
    'unit_number',
    'agreement_value',
    'payout_percent',
    'payout_amount',
    'payout_paid_at',
    'booking_date',
    'agreement_date',
    'invoice_date',
    'invoice_number',
    'invoice_notes',
    'invoiced_at',
    'created_by_id',
])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'configuration_index' => 'integer',
            'agreement_value' => 'integer',
            'payout_percent' => 'decimal:2',
            'payout_amount' => 'integer',
            'payout_paid_at' => 'datetime',
            'booking_date' => 'date',
            'agreement_date' => 'date',
            'invoice_date' => 'date',
            'invoiced_at' => 'datetime',
        ];
    }

    public function hasAgreement(): bool
    {
        return $this->agreement_date !== null;
    }

    public function hasInvoice(): bool
    {
        return $this->invoiced_at !== null;
    }

    public function canMarkAgreement(): bool
    {
        return ! $this->hasAgreement();
    }

    public function canCreateInvoice(): bool
    {
        return $this->hasAgreement() && ! $this->hasInvoice();
    }

    public function hasPaidPayout(): bool
    {
        return $this->payout_paid_at !== null;
    }

    public function canMarkPayoutPaid(): bool
    {
        return $this->hasAgreement() && $this->hasInvoice() && ! $this->hasPaidPayout();
    }

    public static function calculatePayoutAmount(int $agreementValue, float|string|null $payoutPercent): int
    {
        return (int) round($agreementValue * ((float) ($payoutPercent ?? 0) / 100));
    }

    public static function invoiceNumberFor(int $id): string
    {
        return 'INV-'.str_pad((string) $id, 5, '0', STR_PAD_LEFT);
    }

    public function pdfFilename(): string
    {
        return ($this->invoice_number ?? self::invoiceNumberFor($this->id)).'.pdf';
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}

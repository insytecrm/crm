<?php

namespace App\Models;

use App\Enums\GoogleSheetConnectionStatus;
use Database\Factories\GoogleSheetConnectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'created_by_id',
    'name',
    'spreadsheet_url',
    'spreadsheet_id',
    'sheet_title',
    'status',
    'headers',
    'column_map',
    'last_synced_row',
    'last_synced_at',
    'total_synced',
    'total_skipped',
    'total_failed',
    'verified_at',
    'connected_at',
    'last_error',
])]
class GoogleSheetConnection extends Model
{
    /** @use HasFactory<GoogleSheetConnectionFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'draft',
        'last_synced_row' => 1,
        'total_synced' => 0,
        'total_skipped' => 0,
        'total_failed' => 0,
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => GoogleSheetConnectionStatus::class,
            'headers' => 'array',
            'column_map' => 'array',
            'last_synced_row' => 'integer',
            'last_synced_at' => 'datetime',
            'total_synced' => 'integer',
            'total_skipped' => 'integer',
            'total_failed' => 'integer',
            'verified_at' => 'datetime',
            'connected_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeConnected(Builder $query): Builder
    {
        return $query->where('status', GoogleSheetConnectionStatus::Connected);
    }

    public function isConnected(): bool
    {
        return $this->status === GoogleSheetConnectionStatus::Connected;
    }

    public function isPaused(): bool
    {
        return $this->status === GoogleSheetConnectionStatus::Paused;
    }

    public function isVerified(): bool
    {
        return in_array($this->status, [
            GoogleSheetConnectionStatus::Verified,
            GoogleSheetConnectionStatus::Connected,
            GoogleSheetConnectionStatus::Paused,
        ], true);
    }

    public function progressPercent(): int
    {
        $total = $this->total_synced + $this->total_skipped + $this->total_failed;

        if ($total === 0) {
            return $this->isConnected() || $this->isPaused() ? 100 : 0;
        }

        return (int) round(($this->total_synced / $total) * 100);
    }
}

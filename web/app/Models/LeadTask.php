<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Database\Factories\LeadTaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'lead_id',
    'title',
    'description',
    'due_at',
    'remind_at',
    'reminder_before_seconds',
    'reminder_dismissed_at',
    'status',
    'completed_at',
    'completion_notes',
    'cancellation_notes',
    'assigned_to_id',
    'created_by_id',
])]
class LeadTask extends Model
{
    /** @use HasFactory<LeadTaskFactory> */
    use HasFactory;

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'remind_at' => 'datetime',
            'reminder_before_seconds' => 'integer',
            'reminder_dismissed_at' => 'datetime',
            'completed_at' => 'datetime',
            'status' => TaskStatus::class,
        ];
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

    public function isCompleted(): bool
    {
        return $this->status === TaskStatus::Complete;
    }

    public function isCancelled(): bool
    {
        return $this->status === TaskStatus::Cancelled;
    }

    public function isClosed(): bool
    {
        return $this->status->isTerminal();
    }

    public function isOverdue(): bool
    {
        return ! $this->isClosed()
            && $this->due_at !== null
            && $this->due_at->isPast();
    }

    public function statusLabel(): string
    {
        return $this->status->label();
    }
}

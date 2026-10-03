<?php

namespace App\Models;

use App\Enums\AutomationTrigger;
use Database\Factories\AutomationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'name',
    'is_active',
    'trigger',
    'last_run_at',
])]
class Automation extends Model
{
    /** @use HasFactory<AutomationFactory> */
    use HasFactory;

    protected $attributes = [
        'is_active' => false,
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'trigger' => AutomationTrigger::class,
            'last_run_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->whereBelongsTo($user);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<AutomationCondition, $this>
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(AutomationCondition::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<AutomationAction, $this>
     */
    public function actions(): HasMany
    {
        return $this->hasMany(AutomationAction::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<AutomationRun, $this>
     */
    public function runs(): HasMany
    {
        return $this->hasMany(AutomationRun::class)->latest();
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function recipeSummary(): string
    {
        $trigger = $this->trigger?->label() ?? __('No trigger');
        $action = $this->relationLoaded('actions')
            ? $this->actions->first()?->type?->label()
            : null;

        if ($action === null) {
            return $trigger;
        }

        return $trigger.' → '.$action;
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $field ??= $this->getRouteKeyName();

        $query = $this->newQuery()->where($field, $value);

        $user = auth()->user();

        if ($user instanceof User) {
            $query->ownedBy($user);
        }

        return $query->first();
    }
}
